<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Google Meet Link</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Google Meet Link Generator</h1>
        <form id="meetForm" class="shadow p-4 rounded bg-light">
            <div class="form-group">
                <label for="summary">Meeting Summary:</label>
                <input type="text" class="form-control" id="summary" name="summary" required>
            </div>

            <div class="form-group">
                <label for="description">Meeting Description:</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="start_time">Start Time:</label>
                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
            </div>

            <div class="form-group">
                <label for="end_time">End Time:</label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
            </div>

            <div class="form-group">
                <label for="attendees">Attendees (comma-separated emails):</label>
                <input type="text" class="form-control" id="attendees" name="attendees">
            </div>

            <button type="button" class="btn btn-primary btn-block" onclick="generateMeetLink()">Generate Link</button>
        </form>

        <div class="mt-4">
            <h3 class="text-center">Generated Meet Link:</h3>
            <p id="meetLink" class="text-center text-success font-weight-bold"></p>
        </div>
    </div>

    <!-- Bootstrap and jQuery JS CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        async function generateMeetLink() {
            const formData = {
                summary: document.getElementById('summary').value,
                description: document.getElementById('description').value,
                start_time: document.getElementById('start_time').value,
                end_time: document.getElementById('end_time').value,
                attendees: document.getElementById('attendees').value.split(',')
            };

            try {
                const response = await fetch('/generate-meet-link', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                });
                const data = await response.json();
                document.getElementById('meetLink').innerText = data.meet_link || 'Failed to generate link';
            } catch (error) {
                document.getElementById('meetLink').innerText = 'Error generating link';
            }
        }
    </script>
</body>
</html>