jQuery(document).ready(function($) {
    function updateMatchData() {
        // Make an AJAX request to update the match data
        $.ajax({
            url: 'https://api.cricapi.com/v1/cricScore?apikey=ee0a0cbe-115b-4ed9-b055-884d44413c9a', // Replace with the actual API endpoint URL
            type: 'GET',
            success: function(data) {
                // Update the scores on the page
                updateScores(data.data);
            },
            error: function(error) {
                console.error('Error updating match data:', error);
            }
        });
    }

    function updateScores(data) {
        // Iterate through each match data
        data.forEach(function(match) {
            var team1Score = match.t1s || '';
            var team2Score = match.t2s || '';

            // Update the scores on the page
            $('.team1-score').text(team1Score);
            $('.team2-score').text(team2Score);
        });
    }

    // Update match data every 30 seconds (adjust as needed)
    setInterval(updateMatchData, 10000);
});
