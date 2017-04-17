/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function funcMy( forecastData ) {
    var forecast = forecastData.city + ". Прогноз погоды на " + forecastData.date;
    forecast += ": " + forecastData.forecast + ". Максимальная температура:" + forecastData.maxTemp + "C";
    alert( forecast );
}

