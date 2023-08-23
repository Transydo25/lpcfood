(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.type_of_cake = {
    attach: function (context, settings) {
      $(once('type_of_cake_js', '#type_of_cake', context)).each(function () {

        $.getJSON('/all-cake?_format=json&', function (data) {
          let b = [];
          const productSet = new Set(b.map(p => p.idProduct));
          data.forEach((value) => {
            if (!productSet.has(value.idProduct)) {
              b.push({ 'id_product': value.idProduct, 'name_product': value.nameProduct });
              productSet.add(value.idProduct);
            }
          })
          let a = [['Task', 'Hours per Day']];
          b.forEach((value, index) => {

            let thisData = data.filter(element => element.idProduct == value.id_product);

            let quantity = 0;
            if (thisData.length) {
              thisData.forEach((value) => {

                quantity += Number(value.quantity);

              });
            }
            if(value.name_product != "") {
              a.push([value.name_product, quantity]);
            }
          })


          google.charts.load("current", { packages: ["corechart"] });
          google.charts.setOnLoadCallback(drawChart);
          function drawChart() {
            // Some raw data (not necessarily accurate)
            var data = google.visualization.arrayToDataTable(a)
            var options = {
              title: 'Biểu đồ tỷ lệ các loại sản phẩm bán ra',

            };


            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
          }
        })
      })
    }
  }



}(jQuery, Drupal, drupalSettings, once));
