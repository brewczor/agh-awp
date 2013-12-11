




function init() {
	var delay = 1000;
	setInterval(myTimer, delay);
}


function myTimer()
{
	console.log("myTimer");
	var d = new Date();
	var t = d.toLocaleTimeString();

	var timeTable = t.split(":")

	updateChartData([parseInt(timeTable[0]),parseInt(timeTable[1]),parseInt(timeTable[2])])
	//$('#resultsArea').html(t);
}



function updateChartData(remoteData) {
	var polarChartData = [
		{
			value : remoteData[0],
			color : "#D97041"
		},
		{
			value : remoteData[1],
			color : "#C7604C"
		},
		{
			value : remoteData[2],
			color : "#21323D"
		}
	]

	var ctx = document.getElementById("chart").getContext("2d");
	new Chart(ctx).PolarArea(polarChartData, {animation : false, 
		scaleOverride : true, scaleSteps : 6, scaleStepWidth : 10, scaleStartValue : 0});

}


function updateChartData1(remoteLabels, remoteData) {
	var barChartData = {
		labels : remoteLabels,
		datasets : [
			{
				fillColor : "rgba(0,220,220,0.5)",
				strokeColor : "rgba(220,0,220,1)",
				data : remoteData
			}
		]
	}

	var ctx = document.getElementById("chart").getContext("2d");
	new Chart(ctx).Bar(barChartData, {animation : false, 
		scaleOverride : true, scaleSteps : 60, scaleStepWidth : 1, scaleStartValue : 0});

}