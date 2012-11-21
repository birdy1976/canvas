// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript required by the canvas question type.
 *
 * @package    qtype
 * @subpackage canvas
 * @copyright  2012 Martin Vögeli (Voma) {@link http://moodle.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_canvas = M.qtype_canvas || {};

M.qtype_canvas.init = function (Y) {

	// * * * * *
	// Declaring variables
	// * * * * *
	var t; // temporary variable
	var strDummy = "http://moodle.ch/-/pix/dummy.gif"; // dummy image
	var intRadius = 7; // default radius
	var arrDivCanvas = document.getElementsByClassName('qtype_canvas');
	var arrDivRightAnswer = document.getElementsByClassName('rightanswer');
	// Determine modus
	var flgAttempt = (window.location.pathname.indexOf("/attempt.php") != -1 ? true : false);
	var flgEdit = (window.location.pathname.indexOf("/question.php") != -1 ? true : false);
	var flgPreview = (window.location.pathname.indexOf("/preview.php") != -1 ? true : false);
	var flgPreviewReview = (window.location.search.indexOf("previewid=") != -1 ? true : false);
	var flgReview = (window.location.pathname.indexOf("/review.php") != -1 ? true : false);
	// Show right answer?
	var flgRightAnswer = (arrDivRightAnswer.length != 0 ? true : false);
	// * * * * *
	// Declaring functions
	// * * * * *
	function getById(id){return document.getElementById(id);}
	// // // // //
	// To do: function hideElement(){}
	// // // // //
	// Transform x0,y0,x1,y1,x2,y2 into array
	function getArray(strArray){
		var arr1 = strArray.split(",");
		var arr2 = new Array();
		for (var i in arr1){
			i = parseInt(i);
			if(i%2 == 0){
				arr2[i/2]    = new Array();
				arr2[i/2][0] = parseInt(arr1[i]); // x
				arr2[i/2][1] = parseInt(arr1[i+1]); // y
				arr2[i/2][2] = false;
			}
		}
		return arr2;
	}
	// Draw stored points onto canvas
	function drawArray(canvas, rgba){
		var can = getById(canvas.id);
		var ctx = can.getContext('2d');
		var intRadius = parseInt(getById(canvas.strRadius).value);
		// // // // //
		var strS = getById(canvas.strAnswer).value;
		var arrS = getArray(strS); // Student
		// // // // //
		if(flgRightAnswer && (canvas.strClass == "correct" || canvas.strClass == "incorrect")){
			var intRadius2 = 4*intRadius*intRadius;
			var intDiameter = 2*intRadius;
			var strT = getById(canvas.strSolution).value;
			var arrT = getArray(strT); // Teacher
			var intDiffX; var intDiffY;
			for(var i in arrT) {
				ctx.fillCircle(arrT[i][0], arrT[i][1], intRadius, 'rgba(0,0,0,0.06)');
				for(var j in arrS) {
					intDiffX = arrT[i][0]-arrS[j][0];
					if(intDiameter-intDiffX >= 0){
						intDiffY = arrT[i][1]-arrS[j][1];
						if(intDiameter-intDiffY >= 0){
							var fltDistance2 = Math.pow(intDiffX,2) + Math.pow(intDiffY,2);
							if(intRadius2-fltDistance2 >= 0){
								arrS[j][2] = true; arrT[i][2] = true;
								ctx.fillCircle(arrS[j][0], arrS[j][1], intRadius, 'rgba(0,255,0,0.06)');
							}
						}
					}
				}			
			}
			for(var j in arrS) {
				if(!arrS[j][2]){ctx.fillCircle(arrS[j][0], arrS[j][1], intRadius, 'rgba(255,0,0,0.06)');}
			}
		}else{
			for(var j in arrS) {
				ctx.fillCircle(arrS[j][0], arrS[j][1], intRadius, 'rgba(0,0,255,0.06)');
			}
			
		}
	}
	// Load image into canvas
	function getImg (container, rgba) {
		var canvas = getById(container.id+"canvas");
		var img = new Image();
		var imgTemp = new Image();
		// Wait until image has loaded
		// http://www.tek-tips.com/viewthread.cfm?qid=1527581
		imgTemp.onload=function() {
			img.src = canvas.strSRC;
			var can = getById(canvas.id);
			var ctx = can.getContext('2d');
			can.width = img.width;
			can.height = img.height;
			ctx.drawImage(img, 0, 0, img.width, img.height);
			// * * * * *
			drawArray(canvas, rgba);
			// * * * * *
		}
		imgTemp.src = canvas.strSRC;
	}
	// * * * * *
	// Execute programm
	// * * * * *
	// // // // //
	// Hide divs with class "rightanswer"
	if(flgRightAnswer){
		for (var j in arrDivRightAnswer){
			t = arrDivRightAnswer[parseInt(j)]; if(t != null){t.style.display = "none";}
		}
	}
	// Do some stuff ;)
	for (var j in arrDivCanvas){
		// j = parseInt(j);
		t = arrDivCanvas[parseInt(j)].getAttribute('id');
		var container = getById(t);
		if(container.flgCanvas == undefined){
			// Add canvas only once
			container.flgCanvas = true;
			var strID = container.id;
			var strAnswer = (flgEdit ? "id_answer_0" : strID + "answer");
			// Teacher solution (only in review mode)
			var strSolution = strID + "solution";
			var strURL = (flgEdit ? "id_answer_1" : strID + "url");
			var strSRC = getById(strURL).value;
			if(strSRC == ""){strSRC = strDummy;}
			var strRadius = (flgEdit ? "id_answer_2" : strID + "radius");
			var rgba = 'rgba(0,0,255,0.06)';
			// * * * * *
			// Hide some stuff from user
			// * * * * *
			if(flgEdit){
				t = getById("answerhdr_0"); if(t != null){t.style.display = "none";}
				t = getById("answerhdr_1"); if(t != null){t.style.display = "none";}
				t = getById("answerhdr_2"); if(t != null){t.style.display = "none";}
				t = getById("answerhdr_3"); if(t != null){t.style.display = "none";}
				t = getById("answerhdr_4"); if(t != null){t.style.display = "none";}
				t = getById("answerhdr_5"); if(t != null){t.style.display = "none";}
				// 
				t = getById("id_addanswers"); if(t != null){t.parentNode.parentNode.style.display = "none";}

				// * * * * *
				// Load saved and other values
				// * * * * *
				// Load or set dummy image source
				if(getById(strURL).value == ""){getById(strURL).value = strDummy;}

					// getById("id_url").value = getById(strURL).value;

				// Load or set default point radius
				if(getById(strRadius).value == ""){getById(strRadius).value = intRadius;}
				getById("id_radius").selectedIndex = (getById(strRadius).value-1)/2;
				// Hack I hope nobody will ever see :}
				getById("id_fraction_0").selectedIndex = 1;
				// * * * * *
				// Change saved values
				// * * * * *
				getById(strURL).changeURL = function (strChangeURL) {
					var canvas = getById(container.id+"canvas");
					canvas.strSRC = strChangeURL;
					getById(strURL).value = strChangeURL;
					getById(strAnswer).value = "";
					getImg(container, rgba);
				}
				getById("id_radius").onchange = function () {
					getById(strRadius).value = 1+2*getById("id_radius").value;
					// * * * * *
					getImg(container, rgba);
					// * * * * *
				}
			}
			if(flgAttempt || flgPreview || flgPreviewReview || flgReview){
				t = getById(strID+"answer"); if(t != null){t.style.display = "none";}
			}
			// * * * * *
			// Create and draw into canvas 
			// * * * * *
			// Creates a new canvas element and appends it as a child
			// to the parent element, and returns the reference to
			// the newly created canvas element
			function createCanvas(parent, width, height) {
				var canvas = {};
				canvas.node = document.createElement('canvas');
				canvas.context = canvas.node.getContext('2d');
				canvas.node.width = width || 100;
				canvas.node.height = height || 100;
				canvas.node.id = parent.id+"canvas";
				// Set variables
				canvas.node.strAnswer = strAnswer;
				canvas.node.strSolution = strSolution;
				canvas.node.strRadius = strRadius;
				canvas.node.strSRC = strSRC;
				canvas.node.strClass = strClass = getById(canvas.node.strAnswer).className;
				canvas.node.onload = function(){
					var can = getById(parent.id+'canvas');
					var ctx = can.getContext('2d');
					can.width = img.width;
					can.height = img.height;
					ctx.drawImage(img, 0, 0, img.width, img.height);
				}
				//
				parent.appendChild(canvas.node);
				if(!flgReview && !flgPreviewReview){
					// Add a break line
					var br = {};
					br.node = document.createElement('br');
					parent.appendChild(br.node);
					// Add a reset button
					var reset = {};
					reset.node = document.createElement('input');
					reset.node.id = strID+"reset";
					reset.node.type = "button";
					reset.node.value = "Reset";
					if(strClass == "correct" || strClass == "incorrect"){
						reset.node.disabled = "disabled";
					}
					reset.node.onclick = function(){
						var img = new Image();
						var imgTemp = new Image();
						imgTemp.onload=function() {
							var can = getById(parent.id+'canvas');
							img.src = can.strSRC;
							var ctx = can.getContext('2d');
							can.width = img.width;
							can.height = img.height;
							ctx.drawImage(img, 0, 0, img.width, img.height);
						}
						imgTemp.src = strSRC;
						getById(flgEdit ? "id_answer_0" : parent.id + "answer").value = "";
					}
					parent.appendChild(reset.node);
				}
				return canvas;
			}
			function initCanvas(container, width, height, fillColor) {
				// Variables
				var canvas = createCanvas(container, width, height);
				var ctx = canvas.context;
				// define a custom fillCircle method
				ctx.fillCircle = function(x, y, radius, fillColor) {
					this.fillStyle = fillColor;
					this.beginPath();
					this.moveTo(x, y);
					this.arc(x, y, radius, 0, Math.PI * 2, false);
					this.fill();
				};
				ctx.clearTo = function(fillColor) {
					ctx.fillStyle = fillColor;
					ctx.fillRect(0, 0, width, height);
				};
				ctx.clearTo(fillColor || "#ddd");
				// bind mouse events
				canvas.node.onmousemove = function(e) {
					if (!canvas.isDrawing) {
						return;
					}
					// http://api.jquery.com/offset/
					// Alternative: http://new.davglass.com/files/yui/dd34/
					// Dom.getXY(el): http://yuilibrary.com/yui/docs/node/
					// http://docs.moodle.org/dev/JavaScript_guidelines
					// http://docs.moodle.org/dev/YUI
					var offset = $(this).offset();
					var x = Math.round(e.pageX - offset.left);
					var y = Math.round(e.pageY - offset.top);
					ctx.radius = getById(this.strRadius).value;
					var fillColor = 'rgba(0,0,255,0.06)';
					t = (getById(this.strAnswer).value != "") ? "," : "";
					getById(this.strAnswer).value += t + x + "," + y;
					ctx.fillCircle(x, y, ctx.radius, fillColor);
				};
				if(!flgReview && !flgPreviewReview){
					canvas.node.onmousedown = function(e) {canvas.isDrawing = true;};
					canvas.node.onmouseup   = function(e) {canvas.isDrawing = false;};
					canvas.node.onmouseout  = canvas.node.onmouseup;
				}
				getImg(container, 'rgba(0,0,255,0.06)');
			}
			// Initialize canvas
			initCanvas(container, 480, 360, '#ddd');
		}
	}
};
