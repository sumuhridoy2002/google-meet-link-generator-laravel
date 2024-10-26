<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    # Authenticate using a Gmail account to obtain a Google access token, which grants the permission to generate a meeting.
    public function redirectToGoogle()
    {
        $client = new Client();
        $client->setAuthConfig(env('GOOGLE_CALENDAR_CREDENTIALS_PATH'));
        $client->setRedirectUri(env('GOOGLE_CALENDAR_REDIRECT_URI'));
        $client->addScope(Calendar::CALENDAR_EVENTS);

        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    # Define a callback route that will be called by Google after successful authentication.
    public function handleGoogleCallback(Request $request)
    {
        $client = new Client();
        $client->setAuthConfig(env('GOOGLE_CALENDAR_CREDENTIALS_PATH'));
        $client->setRedirectUri(env('GOOGLE_CALENDAR_REDIRECT_URI'));

        if ($request->get('code')) {
            $client->fetchAccessTokenWithAuthCode($request->get('code'));

            session(['google_access_token' => $client->getAccessToken()]);
            
            # Redirect back to your form or other page
            return redirect('/');
        }

        return redirect()->route('google-auth');
    }

    # Request to generate a meeting
    public function createGoogleCalendarEvent(Request $request)
    {
        # Check if access token exists in the session
        if (!session()->has('google_access_token')) {
            return redirect()->route('google-auth')->with('error', 'Please authenticate with Google.');
        }

        # Set up the Google client and Calendar service
        $client = new Client();
        $client->setAccessToken(session('google_access_token'));

        # Check if the access token has expired
        if ($client->isAccessTokenExpired()) {
            return redirect()->route('google-auth')->with('error', 'Access token expired. Please re-authenticate.');
        }

        # Create the Calendar service instance
        $calendarService = new Calendar($client);

        # Prepare the start and end times using Carbon for proper formatting
        $startTime = \Carbon\Carbon::parse($request->input('start_time'))->toISOString();
        $endTime = \Carbon\Carbon::parse($request->input('end_time'))->toISOString();

        # Create a new event using data from the request
        $event = new Event([
            'summary' => $request->input('summary', 'Sample Meeting'),
            'description' => $request->input('description', 'Meeting Description'),
            'start' => [
                'dateTime' => $startTime,
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => $endTime,
                'timeZone' => 'America/Los_Angeles',
            ],
            # Ensure input is treated as a string and split accordingly
            'attendees' => array_map(function ($email) {
                return ['email' => trim($email)];
            }, is_array($request->input('attendees')) ? $request->input('attendees') : explode(',', $request->input('attendees', ''))),
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ],
                    'requestId' => uniqid(),
                ],
            ],
        ]);


        try {
            # Insert the event into the primary calendar
            $createdEvent = $calendarService->events->insert('primary', $event, ['conferenceDataVersion' => 1]);
            return response()->json(['meet_link' => $createdEvent->hangoutLink]);
        } catch (\Google\Service\Exception $e) {
            # Log the detailed error message for debugging
            Log::error('Google Calendar API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create event: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            # Catch any other exceptions and log them
            Log::error('General Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create event due to an unexpected error.'], 500);
        }
    }
}