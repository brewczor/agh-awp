var fileChooser;
var dropZone;

function initialize() {
    fileChooser = document.getElementById('fileinput');
    fileChooser.addEventListener('change', handleFileSelection, false);

    //drop
    dropZone = document.getElementById('drop_zone');
    dropZone.addEventListener('dragover', handleDragOver, false);
    dropZone.addEventListener('drop', handleFileSelectDrop, false);
}


function clickInput() {
    console.log("clickInput")
    document.getElementById('fileinput').click();
}


function saveChart() {
    var canvas = document.getElementById("chart");
    var img    = canvas.toDataURL("image/png");
    //document.write('<img id="canvasImg" src="'+img+'"/>');
    
    window.open(img, "toDataURL() image", "width=620, height=460");

}


function showElement(id) {
    console.log("showStuff: " + id);
    document.getElementById(id).style.display = 'inline';
}


function hideElement(id) {
    document.getElementById(id).style.display = 'none';
}


//drop version
function handleDragOver(evt) {
    evt.stopPropagation();
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
}

function handleFileSelectDrop(evt) {
    evt.stopPropagation();
    evt.preventDefault();

    var file = evt.dataTransfer.files[0]; // FileList object.

    var reader = new FileReader();

    waitForTextReadComplete(reader);
    reader.readAsText(file);
  }


//standart upload
function handleFileSelection() {
    console.log("hadleFileSelection");
    var file = fileChooser.files[0];
    var reader = new FileReader();

    waitForTextReadComplete(reader);
    reader.readAsText(file);
}

function waitForTextReadComplete(reader) {
    console.log("waitForTextRead");
    reader.onloadend = function (event) {
        var text = event.target.result;
        parseTextAsXml(text);
    }
}

function parseTextAsXml(text) {
    console.log("parseTextAsXml");
    var parser = new DOMParser();
    var xmlDOM = parser.parseFromString(text, "text/xml");

    //generating chart
    generateChart(xmlDOM);
}


function showChartElements() {
    showElement("chart");
    showElement("btnP");
    hideElement("descText");
}

function generateChart(xmlDOM) {
    console.log("generateChart");

    showChartElements();

    var mainNode = xmlDOM.getElementsByTagName("chart")[0];
    console.log("mainNode: " + mainNode);

    var chartAtts = mainNode.attributes;
    var chartName = chartAtts.getNamedItem("name").nodeValue;
    document.getElementById("chartHeader").innerHTML = chartName;

    var chartType = chartAtts.getNamedItem("type").nodeValue;
    console.log("type: " + chartType);

    //do wywalenia
    console.log("mainNode: " + mainNode);
    console.log("mainNode att: " + chartAtts);
    console.log("mainNode att name: " + chartName);
    x = xmlDOM.getElementsByTagName("point").length;
    console.log("Point: " + x);

    switch (chartType) {

        case "bar":
            console.log("bar");
            var barChartData = generateBarChartData(mainNode);
            generateBarChart(barChartData);
            break;
        case "doughnut":
            console.log("doughnut");
            var douhnutChartData = generateDoughnutChartData(mainNode);
            generateDoughnutChart(douhnutChartData);
            break;
        case "line":
            console.log("line");
            var lineChartData = generateLineChartData(mainNode);
            generateLineChart(lineChartData);
            break;
        case "pie":
            console.log("pie");
            var pieChartData = generatePieChartData(mainNode);
            generatePieChart(pieChartData);
            break;
        case "polar":
            console.log("polar");
            var polarAreaChartData = generatePolarAreaChartData(mainNode);
            generatePolarAreaChart(polarAreaChartData);
            break;
        case "radar":
            console.log("radar");
            var radarChartData = generateRadarChartData(mainNode);
            generateRadarChart(radarChartData);
            break;
        default:
            console.log("defaut");
            //var lineChartData = generateLineChartData();
            //generateLineChart(lineChartData);
    }
}


function generateLabelValues(mainNode) {
    var labelNodes = mainNode.getElementsByTagName("labels")[0].getElementsByTagName("label");
    var labelValues = [];
    for (i = 0; i < labelNodes.length; i++) {
        labelValues.push(labelNodes[i].childNodes[0].nodeValue);
    }

    return labelValues;
}


// BAR
function generateBarChartData(mainNode) {
    //labels
    var labelValues = generateLabelValues(mainNode);
    //console.log("labelValues: " + labelValues);

    //data
    var dataSetsValue = [];

    var dataNodes = mainNode.getElementsByTagName("data");
    
    for(k = 0; k < dataNodes.length; k++) {
        var styleNode = dataNodes[k].getElementsByTagName("style")[0];

        //fillColor
        var fillColorAtt = styleNode.getElementsByTagName("fillColor")[0].attributes;
        var fillColorValue = "rgba(" + Number(fillColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("fillColorValue: " + fillColorValue);

        //strokeColor
        var strokeColorAtt = styleNode.getElementsByTagName("strokeColor")[0].attributes;
        var strokeColorValue = "rgba(" + Number(strokeColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("strokeColorValue: " + strokeColorValue);


        var pointNodes = dataNodes[k].getElementsByTagName("point");

        var pointValueArray = [];
        for (i = 0; i < pointNodes.length; i++) {
            pointValueArray.push(Number(pointNodes[i].attributes.getNamedItem("value").nodeValue));
        }

        //console.log("pointValueArray: " + pointValueArray);

        dataSetsValue.push({
            fillColor: fillColorValue,
            strokeColor: strokeColorValue,
            data: pointValueArray
        });

    }

    var chartData = {
        labels: labelValues,
        datasets: dataSetsValue
    }

    return chartData;
}


function generateBarChart(barChartData) {
    var myBar = new Chart(document.getElementById("chart").getContext("2d")).Bar(barChartData);
}


// DOUGHNUT
function generateDoughnutChartData(mainNode) {
    var chartData =  [];

    //data
    var dataNodes = mainNode.getElementsByTagName("data");

    var pointNodes = dataNodes[0].getElementsByTagName("point");
    for(i = 0; i < pointNodes.length; i++) {
        var pointAtt = pointNodes[i].attributes;
        var pointValue = Number(pointAtt.getNamedItem("value").nodeValue);

        var pointColorValue = "rgba(" + Number(pointAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointAtt.getNamedItem("a").nodeValue) + ")";

        chartData.push({value: pointValue, color: pointColorValue});
        //console.log("TU: " + {value: pointValue, color: pointColorValue});
    }

    return chartData;
}

function generateDoughnutChart(doughnutChartData) {
    var myDoughnut = new Chart(document.getElementById("chart").getContext("2d")).Doughnut(doughnutChartData);
}


//LINE
function generateLineChartData(mainNode) {
    //labels
    var labelValues = generateLabelValues(mainNode);

    var dataSetsValue = [];

    var dataNodes = mainNode.getElementsByTagName("data");
    
    for(k = 0; k < dataNodes.length; k++) {
        var styleNode = dataNodes[k].getElementsByTagName("style")[0];

        //fillColor
        var fillColorAtt = styleNode.getElementsByTagName("fillColor")[0].attributes;
        var fillColorValue = "rgba(" + Number(fillColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("fillColorValue: " + fillColorValue);

        //strokeColor
        var strokeColorAtt = styleNode.getElementsByTagName("strokeColor")[0].attributes;
        var strokeColorValue = "rgba(" + Number(strokeColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("strokeColorValue: " + strokeColorValue);


        //pointColor
        var pointColorAtt = styleNode.getElementsByTagName("pointColor")[0].attributes;
        var pointColorValue = "rgba(" + Number(pointColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("pointColorValue: " + pointColorValue);

        //pointColor
        var pointStrokeColorAtt = styleNode.getElementsByTagName("pointStrokeColor")[0].attributes;
        var pointStrokeColorValue = "rgba(" + Number(pointStrokeColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("pointColorValue: " + pointColorValue);


        var pointNodes = dataNodes[k].getElementsByTagName("point");

        var pointValueArray = [];
        for (i = 0; i < pointNodes.length; i++) {
            pointValueArray.push(Number(pointNodes[i].attributes.getNamedItem("value").nodeValue));
        }

        //console.log("pointValueArray: " + pointValueArray);

        dataSetsValue.push({
            fillColor: fillColorValue,
            strokeColor: strokeColorValue,
            pointColor: pointColorValue,
            pointStrokeColor: pointStrokeColorValue,
            data: pointValueArray
        });

    }

    var chartData = {
        labels: labelValues,
        datasets: dataSetsValue
    }

    return chartData;
}

function generateLineChart(lineChartData) {
    var myLine = new Chart(document.getElementById("chart").getContext("2d")).Line(lineChartData);
}

//PIE
function generatePieChartData(mainNode) {
    var chartData =  [];

    console.log("mainNode pie" + mainNode);
    //data
    var dataNodes = mainNode.getElementsByTagName("data");

    var pointNodes = dataNodes[0].getElementsByTagName("point");
    for(i = 0; i < pointNodes.length; i++) {
        var pointAtt = pointNodes[i].attributes;
        var pointValue = Number(pointAtt.getNamedItem("value").nodeValue);

        var pointColorValue = "rgba(" + Number(pointAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointAtt.getNamedItem("a").nodeValue) + ")";

        chartData.push({value: pointValue, color: pointColorValue});
    }

    return chartData;
}


function generatePieChart(pieChartData) {
   var myPie = new Chart(document.getElementById("chart").getContext("2d")).Pie(pieChartData);
}


//POLAR AREA  to nie dziala chuj wie czemu
function generatePolarAreaChartData(mainNode) {
    var chartData = [];

    console.log("mainNode polar" + mainNode);
    //data
    var dataNodes = mainNode.getElementsByTagName("data");

    var pointNodes = dataNodes[0].getElementsByTagName("point");
    for(i = 0; i < pointNodes.length; i++) {
        var pointAtt = pointNodes[i].attributes;
        var pointValue = Number(pointAtt.getNamedItem("value").nodeValue);

        var pointColorValue = "rgba(" + Number(pointAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointAtt.getNamedItem("a").nodeValue) + ")";

        chartData.push({value: pointValue, color: pointColorValue});
    }

    return chartData;
}



function generatePolarAreaChart(polarAreaChartData) {
    var myPolarArea = new Chart(document.getElementById("chart").getContext("2d")).PolarArea(polarAreaChartData);
}



function generateRadarChartData(mainNode) {
   //labels
    var labelValues = generateLabelValues(mainNode);

    var dataSetsValue = [];

    var dataNodes = mainNode.getElementsByTagName("data");
    
    for(k = 0; k < dataNodes.length; k++) {
        var styleNode = dataNodes[k].getElementsByTagName("style")[0];

        //fillColor
        var fillColorAtt = styleNode.getElementsByTagName("fillColor")[0].attributes;
        var fillColorValue = "rgba(" + Number(fillColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(fillColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("fillColorValue: " + fillColorValue);

        //strokeColor
        var strokeColorAtt = styleNode.getElementsByTagName("strokeColor")[0].attributes;
        var strokeColorValue = "rgba(" + Number(strokeColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(strokeColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("strokeColorValue: " + strokeColorValue);


        //pointColor
        var pointColorAtt = styleNode.getElementsByTagName("pointColor")[0].attributes;
        var pointColorValue = "rgba(" + Number(pointColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("pointColorValue: " + pointColorValue);

        //pointColor
        var pointStrokeColorAtt = styleNode.getElementsByTagName("pointStrokeColor")[0].attributes;
        var pointStrokeColorValue = "rgba(" + Number(pointStrokeColorAtt.getNamedItem("r").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("g").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("b").nodeValue) + "," +
            Number(pointStrokeColorAtt.getNamedItem("a").nodeValue) + ")";

        //console.log("pointColorValue: " + pointColorValue);


        var pointNodes = dataNodes[k].getElementsByTagName("point");

        var pointValueArray = [];
        for (i = 0; i < pointNodes.length; i++) {
            pointValueArray.push(Number(pointNodes[i].attributes.getNamedItem("value").nodeValue));
        }

        //console.log("pointValueArray: " + pointValueArray);

        dataSetsValue.push({
            fillColor: fillColorValue,
            strokeColor: strokeColorValue,
            pointColor: pointColorValue,
            pointStrokeColor: pointStrokeColorValue,
            data: pointValueArray
        });

    }

    var chartData = {
        labels: labelValues,
        datasets: dataSetsValue
    }

    return chartData;
}


function generateRadarChart(radarChartData) {
    var myRadar = new Chart(document.getElementById("chart").getContext("2d")).Radar(radarChartData);
}