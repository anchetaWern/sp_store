var sentiments_ctx = document.getElementById('sentiments_chart').getContext("2d");
var sentiments_chart = new Chart(sentiments_ctx).Pie(sentiments_data);