(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.revenue = {
    attach: function (context, settings) {
      $(once('cost_js', '#cost', context)).each(function () {


        $.getJSON('/all-date-order?_format=json&', function (data) {
          const currentYear = new Date().getFullYear();
          let months = [];

          for (let i = 1; i <= 12; i++) {
            months.push(`${currentYear}-${i < 10 ? '0' + i : i}`);
          }
          let a = [['Element', 'Density', { role: 'style' }]];
          months.forEach((date, index) => {
            let thisData = data.filter(element => element.created == date);
            let totalPrice = 0;
            if (thisData.length) {
              thisData.forEach((value) => {
                // console.log(value)
                totalPrice += Number(value.totalPrice);
                // console.log(totalPrice)
                // console.log(value.created)
              });
            }
            a.push([date, totalPrice, "blue"]);

          })

          google.charts.load('current', { 'packages': ['corechart'] });
          google.charts.setOnLoadCallback(drawVisualization);

          function drawVisualization() {
            // Some raw data (not necessarily accurate)
            var data = google.visualization.arrayToDataTable(a);
            var options = {
              title: 'Doanh Thu Chí Phí Hàng(VND)',
              vAxis: { title: 'Doanh Thu-VND' },
              hAxis: { title: 'Tháng' },
              seriesType: 'bars',
              series: { 5: { type: 'line' } }
            };

            var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
            chart.draw(data, options);
          }
        })
      })
    }
  }



}(jQuery, Drupal, drupalSettings, once));
