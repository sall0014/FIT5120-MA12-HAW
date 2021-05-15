$(document).ready(function(){
                //portland
                $.get("https://stayingsafeonrockshelves.tk/wp-content/get-api.php?locations=13034&forecasts=tides", function(dataTide) {
                    var datTide = JSON.parse(dataTide);
                    console.log(datTide);
                    var eachTide = datTide.forecasts.tides.days[0].entries;
                    var today = new Date();  
                       console.log(today.getHours() + ":" + today.getMinutes());
                    listoftime = []
                    
                    function formatAMPM(date) {
                      var hours = date.getHours();
                      var minutes = date.getMinutes();
                      var ampm = hours >= 12 ? 'pm' : 'am';
                      hours = hours % 12;
                      hours = hours ? hours : 12; // the hour '0' should be '12'
                      minutes = minutes < 10 ? '0'+minutes : minutes;
                      var strTime = hours + ':' + minutes + ' ' + ampm;
                      return strTime;
                    }
                    
                    for( var each in eachTide) {
                       var date = new Date(eachTide[each].dateTime)
                       var time = formatAMPM(date)
                       listoftime.push(time);
                    }
                    
                    
                    $.get("https://stayingsafeonrockshelves.tk/wp-content/get-api.php?locations=13034&forecasts=swell", function(dataSwell) {
                        var datSwell = JSON.parse(dataSwell);
                        var eachSwell = datSwell.forecasts.swell.days[0].entries;
//                        var swell = dat_swell.forecasts.swell.days[0].entries[0].height;
                    var rock = 6.1/2
                    var x = [0, 0, 5, 5, 0, 0, 5, 5]
                    var y = [0, 5, 5, 0, 0, 5, 5, 0]
                    var i = [7, 0, 0, 0, 4, 4, 6, 6, 4, 0, 3, 2]
                    var j = [3, 4, 1, 2, 5, 6, 5, 2, 0, 1, 6, 3]
                    var k = [0, 7, 2, 3, 6, 7, 1, 1, 5, 5, 7, 6]
                    
                    var tide;
                    var swell;
                      
                    
                    function getTideTime(chosenTime){
                        for (var i = 0 ; i < eachTide.length ; i++){
                             var dateTide = new Date(eachTide[i].dateTime)
                             var timeTide = formatAMPM(dateTide)
                            if(timeTide  === chosenTime){
                               tide = eachTide[i].height; }
                            
                        }
                        for (var j = 0 ; j < eachSwell.length ; j++){
                             var dateSwell = new Date(eachSwell[j].dateTime)
                             var swellTide = formatAMPM(dateSwell)
                            if(swellTide.split(" ")[1] === chosenTime.split(" ")[1] && 
                               swellTide.split(" ")[0].split(":")[0] === chosenTime.split(" ")[0].split(":")[0]){
                               swell = eachSwell[i].height;}
                            
                        }
                        
                    }
                        
                    setChart(listoftime[0], rock, x, y, i, j, k);
                    
                    function setChart(chosenTime, rock, x, y, i, j, k){
                        getTideTime(chosenTime);
                        console.log(tide);
                        console.log(swell);
                        var trace1 = {
                            x: x,
                            y: y,
                            z: [0, 0, 0, 0, rock, rock, rock, rock],
                            i: i,
                            j: j,
                            k: k,
                          opacity: 1,
                          color: '#8b4513',
                            type: 'mesh3d',
                          flatshading: true,
                          lighting: {facenormalsepsilon: 0},
                          name: "Rock Shelf",
                          images:
                              [{
                                "source": "https://images.plot.ly/language-icons/api-home/matlab-logo.png",
                                "xref": "x",
                                "yref": "paper",
                                "x": 3,
                                "y": 0,
                                "sizex": 0.5,
                                "sizey": 1,
                                "opacity": 1,
                                "xanchor": "right",
                                "yanchor": "middle"
                              }]
                        }

                        var trace2 = {
                            x: x.map(function(item) { 
                            // Increment each item by 1
                            return item + 5; 
                        }),
                            y: y,
                            z: [0, 0, 0, 0, tide, tide, tide, tide],
                            i: i,
                            j: j,
                            k: k,
                          opacity: 0.6,
                          color: '#000039',
                            type: 'mesh3d',
                          flatshading: true,
                          lighting: {facenormalsepsilon: 0},
                          name: "Tide"
                        }

                        var trace3 = {
                            x: x.map(function(item) { 
                            // Increment each item by 1
                            return item + 5; 
                        }),
                            y: y,
                            z: [tide, tide, tide, tide, swell, swell, swell, swell],
                            i: i,
                            j: j,
                            k: k,
                          opacity: 0.6,
                          color: '#00ced1',
                            type: 'mesh3d',
                          flatshading: true,
                          lighting: {facenormalsepsilon: 0},
                          name: "Swell"
                        }
                        
                        var layout = {
                            scene: {
                                xaxis: {
                                     visible: false,
                                     showticklabels: false
                                  },
                                yaxis: {
                                     visible: false,
                                     showticklabels: false
                                  },
                                zaxis: {
                                     visible: false,
                                     showticklabels: false
                                  },
                                camera: {
                                      center: {
                                            x: 0, y: 0, z: 0}, 
                                      eye: { 
                                            x:1.5, y:2.5, z:0.1}, 
                                      up: {
                                            x: 0, y: 0, z: 1}
                                    },
                               }
                        }
                        
                        var config = {responsive: true};
                        
                        var data = [trace1, trace2, trace3];

                        Plotly.newPlot('myDiv', data, layout, config)
                        
                    }
                    var innerContainer = document.querySelector('[data-num="0"'),
                    plotEl = innerContainer.querySelector('.plot'),
                    timeSelector = innerContainer.querySelector('.timeoftheday');
                        
                    function assignOptions(textArray, selector) {
                      for (var i = 0; i < textArray.length;  i++) {
                          var currentOption = document.createElement('option');
                          currentOption.text = textArray[i];
                          selector.appendChild(currentOption);
                      }
                    }
                        
                    assignOptions(listoftime, timeSelector);
                        
                    function updateTide(){
                        setChart(timeSelector.value, rock, x, y, i, j, k);
                    }
                        
                    timeSelector.addEventListener('change', updateTide, false);
                        
                })
            })
            })