var MyLoader = {
	loadClock: function () {
		console.log("loadClock");
		$('#chartDiv').load('clock.html', function () {
			MyLoader.stopAll();

			Clock.init();

		});
	},

	loadBitcoin: function () {
		console.log("loadBitcoin");
		$('#chartDiv').load('bitcoin.html', function () {
			MyLoader.stopAll();

			Bitcoin.init()

		});
	},

	loadRPData: function () {
		console.log("loadRPData");
		$('#chartDiv').load('rpdata.html', function () {
			MyLoader.stopAll();
			RPData.init()
		});
	},

	loadRPCPU: function () {
		console.log("loadRPCPU");
		$('#chartDiv').load('rpcpu.html', function () {
			MyLoader.stopAll();
			RPCPU.init()
		});
	},

	loadRPCPULine: function () {
		console.log("loadRPCPULine");
		$('#chartDiv').load('rpcpuline.html', function () {
			MyLoader.stopAll();
			RPCPULine.init()
		});
	},

	stopAll: function () {
		Clock.stop();
		Bitcoin.stop();
		RPData.stop();
		RPCPU.stop();
		RPCPULine.stop();
	}
}

function test() {
	console.log("test");
	console.log("loadClock");
		$('#chartDiv').load('clock.html', Clock.init());
}


var Clock = {
	delay: 1000,
	interval : null,

	init: function () {
		console.log('clock:init')
		Clock.interval = setInterval(Clock.myTimer, Clock.delay);
	},

	myTimer: function () {
		console.log("myTimer");
		var d = new Date();
		var t = d.toLocaleTimeString();

		var timeTable = t.split(":")

		Clock.updateChartData([parseInt(timeTable[0]),
			parseInt(timeTable[1]), parseInt(timeTable[2])]);
	},

	updateChartData : function (remoteData) {
		var polarChartData = [
			{
				value: remoteData[0],
				color: "#D97041"
			},
			{
				value: remoteData[1],
				color: "#C7604C"
			},
			{
				value: remoteData[2],
				color: "#21323D"
			}
		]

		var ctx = document.getElementById("chart").getContext("2d");
		new Chart(ctx).PolarArea(polarChartData, { animation: false,
			scaleOverride: true, scaleSteps: 6, scaleStepWidth: 10, scaleStartValue: 0
		});
	},


	stop : function() {
		clearInterval(Clock.interval);
	}
}

/////

var Bitcoin = {
	delay: 5000,
	timeout: null,

	init: function () {
		console.log('bitcoin:init')
		$(document).ready(function () {
			Bitcoin.getRemoteData();
		});
	},

	getRemoteData: function () {
		console.log("getRemoteData");
		var requestURL = 'https://bitpay.com/api/rates';

		$.getJSON(requestURL, function (data) {
			//to sie wywoluje jak wroca dane
			console.log(data);
			Bitcoin.parseRemoteData(data);
		});

		Bitcoin.timeout = setTimeout(Bitcoin.getRemoteData, Bitcoin.delay);
	},

	parseRemoteData: function (data) {
		console.log("parseRemoteData");

		var usd = 0;
		var eur = 0;
		var gbp = 0;
		var pln = 0;

		for (var i = 0; i < data.length; i++) {
			if (data[i].code === "USD") {
				console.log(data[i]);
				usd = data[i].rate;
			}

			if (data[i].code === "EUR") {
				console.log(data[i]);
				eur = data[i].rate;
			}

			if (data[i].code === "GBP") {
				console.log(data[i]);
				gbp = data[i].rate;
			}
			if (data[i].code === "PLN") {
				console.log(data[i]);
				pln = data[i].rate;
			}

			if (usd != 0 && eur != 0 && gbp != 0 && pln != 0) {
				break;
			}
		}

		Bitcoin.updateChartData([usd, eur, gbp, pln]);
	},

	minV1: 0,
	minV2: 0,
	minV3: 0,
	minV4: 0,

	updateChartData: function (remoteData) {
		//wyrownanie wykresow
		console.log(Bitcoin.minV1 + " " + Bitcoin.minV2 + " " + Bitcoin.minV3 + " " + Bitcoin.minV4)
		if (Bitcoin.minV1 == 0) {
			Bitcoin.minV1 = remoteData[0] - 1;
		}

		if (Bitcoin.minV2 == 0) {
			Bitcoin.minV2 = remoteData[1] - 1;
		}

		if (Bitcoin.minV3 == 0) {
			Bitcoin.minV3 = remoteData[2] - 1;
		}

		if (Bitcoin.minV4 == 0) {
			Bitcoin.minV4 = remoteData[3] - 1;
		}

		var barChartData1 = {
			labels: ["USD"],
			datasets: [
				{
					fillColor: "rgba(0,0,255,0.5)",
					strokeColor: "rgba(0,0,255,1)",
					data: [remoteData[0]]
				}
			]
		}

		var ctx1 = document.getElementById("chart1").getContext("2d");
		new Chart(ctx1).Bar(barChartData1, { animation: false,
			scaleOverride: true, scaleSteps: 20, scaleStepWidth: 0.1, scaleStartValue: Bitcoin.minV1
		});
		///
		var barChartData2 = {
			labels: ["EUR"],
			datasets: [
				{
					fillColor: "rgba(255,255,0,0.5)",
					strokeColor: "rgba(255,255,0,1)",
					data: [remoteData[1]]
				}
			]
		}

		var ctx2 = document.getElementById("chart2").getContext("2d");
		new Chart(ctx2).Bar(barChartData2, { animation: false,
			scaleOverride: true, scaleSteps: 20, scaleStepWidth: 0.1, scaleStartValue: Bitcoin.minV2
		});
		///
		var barChartData3 = {
			labels: ["GBP"],
			datasets: [
				{
					fillColor: "rgba(0,255,0,0.5)",
					strokeColor: "rgba(0,255,0,1)",
					data: [remoteData[2]]
				}
			]
		}

		var ctx3 = document.getElementById("chart3").getContext("2d");
		new Chart(ctx3).Bar(barChartData3, { animation: false,
			scaleOverride: true, scaleSteps: 20, scaleStepWidth: 0.1, scaleStartValue: Bitcoin.minV3
		});
		///
		var barChartData4 = {
			labels: ["PLN"],
			datasets: [
				{
					fillColor: "rgba(255,0,0,0.5)",
					strokeColor: "rgba(255,0,0,1)",
					data: [remoteData[3]]
				}
			]
		}

		var ctx4 = document.getElementById("chart4").getContext("2d");
		new Chart(ctx4).Bar(barChartData4, { animation: false,
			scaleOverride: true, scaleSteps: 20, scaleStepWidth: 0.1, scaleStartValue: Bitcoin.minV4
		});

	},

	stop: function () {
		clearTimeout(Bitcoin.timeout);
	}
}

/////
var RPData = {
	delay: 1000,
	timeout: null,

	init: function () {
		console.log('rpdata:init')
		$(document).ready(function () {
			RPData.getRemoteData();
		});
	},

	getRemoteData: function () {
		console.log("getRemoteData");
		var requestURL = 'http://89.79.177.93:5984/rpdata/_design/widoki/_view/wszystkie?callback=?';

		$.getJSON(requestURL, {
			limit: 10,
			descending: true
		}, function (data) {
			//to sie wywoluje jak wroca dane
			console.log(data);

			RPData.parseRemoteData(data);

		});

		RPData.timeout = setTimeout(RPData.getRemoteData, RPData.delay);
	},

	parseRemoteData: function (data) {
		console.log("rpdata:parseRemoteData");

		var obj = data.rows;

		var j = 0;
		var cpuData = [];
		var memData = [];
		for (var i = 9; i >= 0; i--) {
			console.log(obj[i].value.CPU + "/" + obj[i].value.Memory);
			cpuData.push(parseFloat(obj[i].value.CPU));
			memData.push(parseFloat(obj[i].value.Memory));
			j++;
		}

		RPData.updateChartData(cpuData, memData);
	},

	updateChartData: function (cpuData, memData) {
		console.log(cpuData + "/" + memData);
		var barChartData = {
			labels: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"],
			datasets: [
				{
					fillColor: "rgba(255,0,0,0.5)",
					strokeColor: "rgba(255,0,0,1)",
					data: cpuData
				},
				{
					fillColor: "rgba(0,0,255,0.5)",
					strokeColor: "rgba(0,0,255,1)",
					data: memData
				}
			]
		}

		var ctx = document.getElementById("chart").getContext("2d");
		new Chart(ctx).Bar(barChartData, { animation: false,
			scaleOverride: true, scaleSteps: 10, scaleStepWidth: 10, scaleStartValue: 0
		});
	},

	stop: function () {
		clearTimeout(RPData.timeout);
	}
}



var RPCPU = {
	delay: 1000,
	timeout: null,

	init: function () {
		console.log('rpcpu:init')
		$(document).ready(function () {
			RPCPU.getRemoteData();
		});
	},

	getRemoteData: function () {
		console.log("getRemoteData");
		var requestURL = 'http://89.79.177.93:5984/rpdata/_design/widoki/_view/wszystkie?callback=?';

		$.getJSON(requestURL, {
			limit: 1,
			descending: true
		}, function (data) {
			//to sie wywoluje jak wroca dane
			console.log(data);

			RPCPU.parseRemoteData(data);

		});

		RPCPU.timeout = setTimeout(RPCPU.getRemoteData, RPCPU.delay);
	},

	parseRemoteData: function (data) {
		console.log("rpcpu:parseRemoteData");

		var cpuValue = data.rows[0].value.CPU;

		RPCPU.updateChartData(parseFloat(cpuValue));
	},

	updateChartData: function (remoteData) {
		console.log(remoteData);
		var doughnutChartData = [
			{
				value: remoteData,
				color: "#F7464A"
			},
			{
				value: 100 - remoteData,
				color: "#00EE76"
			}
		]

		var ctx = document.getElementById("chart").getContext("2d");
		new Chart(ctx).Doughnut(doughnutChartData, { animation: false });
	},

	stop: function () {
		clearTimeout(RPCPU.timeout);
	}
}


var RPCPULine = {
	delay: 1000,
	timeout: null,

	init: function () {
		console.log('rpcpuline:init')
		$(document).ready(function () {
			RPCPULine.getRemoteData();
		});
	},

	getRemoteData: function () {
		console.log("getRemoteData");
		var requestURL = 'http://89.79.177.93:5984/rpdata/_design/widoki/_view/wszystkie?callback=?';

		$.getJSON(requestURL, {
			limit: 30,
			descending: true
		}, function (data) {
			//to sie wywoluje jak wroca dane
			console.log(data);

			RPCPULine.parseRemoteData(data);
		});

		RPCPULine.timeout = setTimeout(RPCPULine.getRemoteData, RPCPULine.delay);
	},

	parseRemoteData: function (data) {
		console.log("rpccpuline:parseRemoteData");

		var obj = data.rows;

		var j = 0;
		var cpuData = [];
		var cpuMemData = [];
		for (var i = 29; i >= 0; i--) {
			//console.log(obj[i].value.CPU);
			cpuData.push(parseFloat(obj[i].value.CPU));
			cpuMemData.push(parseFloat(obj[i].value.Memory));
			j++;
		}

		RPCPULine.updateChartData(cpuData, cpuMemData);
	},

	updateChartData: function (cpuData, cpuMemData) {
		//console.log(cpuData);
		var lineChartData = {
			labels: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
				"10", "11", "12", "13", "14", "15", "16", "17", "18", "19",
				"20", "21", "22", "23", "24", "25", "26", "27", "28", "29"],
			datasets: [
				{
					fillColor: "rgba(0,0,255,0.2)",
					strokeColor: "rgba(0,0,255,1)",
					pointColor: "rgba(0,0,220,1)",
					pointStrokeColor: "#fff",
					data: cpuMemData
				},
				{
					fillColor: "rgba(255,0,0,0.6)",
					strokeColor: "rgba(255,0,0,1)",
					pointColor: "rgba(220,0,0,1)",
					pointStrokeColor: "#fff",
					data: cpuData
				}
			]
		}

		var ctx = document.getElementById("chart").getContext("2d");
		new Chart(ctx).Line(lineChartData, { animation: false,
			scaleOverride: true, scaleSteps: 20, scaleStepWidth: 5, scaleStartValue: 0
		});
	},

	stop: function () {
		clearTimeout(RPCPULine.timeout);
	}
}