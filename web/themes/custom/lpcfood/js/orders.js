/**
 * @file
 * Belgrade Theme JS.
 */
(function ($, Drupal, once) {

  'use strict';

  /**
   * Close behaviour.
   */
  Drupal.behaviors.orders = {
    attach: function (context) {
      // Date pickup.
      $('[data-drupal-selector="edit-field-pickup-date-value"]').attr('type','date');
      // Search form.
      $(".order-search").once().on("change keyup", function () {
        var txt = $(this).val();
        $('.item-list li').hide();
        $('.item-list li').each(function () {
          if ($(this).text().toUpperCase().indexOf(txt.toUpperCase()) != -1) {
            $(this).show();
          }
        });
      });

      // Counting order by status.
      function orderStatus() {
        var arrStatus = [];
        $('.orderStatus').each(function () {
          let status = $(this).data('status');
          if (arrStatus[status] === undefined) {
            arrStatus[status] = 0;
          }
          arrStatus[status]++;
        });
        for (var item in arrStatus) {
          $('#state-' + item).html(arrStatus[item]);
        }
      }
      orderStatus();

      // Set value modal.
      $('.views-field-field-phone .btn-link').once().on('click', function (){
        let value = $(this).data('bs-whatever');
        let idInput = $(this).data('input');
        $('#' + idInput).val(value);
      });

      // Open cancel Modal.
      $('[data-drupal-selector="edit-cancel"]').once().on('click',function (event){
        event.preventDefault();
        let form = $(this).closest('form');
        let order_id = form.data('drupal-selector').split('-').at(-1);
        $('form .order-cancel-id').val(order_id);
        let cancelModal = new bootstrap.Modal(document.getElementById('block-ordercancelblock'));
        cancelModal.show();
      });

      function showChart(data){
        console.log(data);

        // Graphs
        const ctx = document.getElementById('week-chart').getContext('2d');
        const options = {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          legend: {
            display: false
          }
        };
        const weekChart = new Chart(ctx, {
          type: 'line',
          options: options,
          data: {
            labels: Object.keys(data),
            datasets: [{
              data: data,
              lineTension: 0,
              backgroundColor: 'transparent',
              borderColor: 'orange',
              borderWidth: 4,
              pointBackgroundColor: 'darkorange'
            }]
          },
        });
      }
      // Open Dashboard.
      $('[data-bs-target="#block-dashboard"]').once().on('click', function (event) {
        // Get data.
        let protocol = $(location).attr("protocol");
        let hostname = $(location).attr("hostname");
        let url = protocol + "//" + hostname;
        const storeUuid = $(this).data('store-uuid');
        $.get(url + "/session/token").done(function (response) {
          let csrfToken = response;
          $.ajax({
            url: url + "/pizzahips_dashboard/data/" + storeUuid + "?_format=json",
            method: 'GET',
            headers: {
              'X-CSRF-Token': csrfToken,
              'Content-Type': 'application/json',
              "Accept": "application/json",
            },
            dataType: "json",
            success: function (dataDashboard) {
              console.info(dataDashboard);
              if(dataDashboard){
                let sales = dataDashboard.sales;
                let topProducts = dataDashboard.topProducts;
                let increased = '<i class="bi bi-graph-up-arrow me-2"></i>';
                let decreased = '<i class="bi bi-graph-down-arrow me-2"></i>';
                $('#dashboard-sale-today .amount').text(sales.today.amount.toLocaleString('vi', {style : 'currency', currency : 'VND'}));
                $('#dashboard-sale-today .changeIndicator').text(Drupal.t(sales.today.changeIndicator)).prepend(sales.today.changeIndicator=="increased" ? increased : decreased).append(": "+sales.today.changePercentage + "%");
                  $('#dashboard-sale-today .changeIndicator').addClass(sales.today.changeIndicator=="increased" ? 'text-success' : 'text-danger');
                  $('#dashboard-sale-today .card-header').addClass(sales.today.changeIndicator=="increased" ? 'bg-qualified ' : 'bg-unsatisfactory');
                  $('#dashboard-sale-yesterday .amount').text(sales.yesterday.amount.toLocaleString('vi', {style : 'currency', currency : 'VND'}));
                  $('#dashboard-sale-yesterday .changeIndicator').text(Drupal.t(sales.yesterday.changeIndicator)).prepend(sales.yesterday.changeIndicator=="increased" ? increased : decreased).append(": "+sales.yesterday.changePercentage + "%");
                  $('#dashboard-sale-yesterday .changeIndicator').addClass(sales.yesterday.changeIndicator=="increased" ? 'text-success' : 'text-danger');
                  $('#dashboard-sale-yesterday .card-header').addClass(sales.yesterday.changeIndicator=="increased" ? 'bg-qualified ' : 'bg-unsatisfactory');
                  $('#dashboard-sale-week .amount').text(sales.week.amount.toLocaleString('vi', {style : 'currency', currency : 'VND'}));
                  $('#dashboard-sale-week .changeIndicator').text(Drupal.t(sales.week.changeIndicator)).prepend(sales.week.changeIndicator=="increased" ? increased : decreased).append(": "+sales.week.changePercentage + "%");
                  $('#dashboard-sale-week .changeIndicator').addClass(sales.week.changeIndicator=="increased" ? 'text-success' : 'text-danger');
                  $('#dashboard-sale-week .card-header').addClass(sales.week.changeIndicator=="increased" ? 'bg-qualified ' : 'bg-unsatisfactory');
                  $('#dashboard-topProducts-0 .amount').text(Number(topProducts[0].total).toLocaleString('vi', {style : 'currency', currency : 'VND'}));
                  const topProductText = `${topProducts[0].title}: <b>${Number(topProducts[0].total).toLocaleString('vi', {style : 'currency', currency : 'VND'})}</b> <i class="bi bi-cart-check"></i> ${topProducts[0].purchases}`;
                  $('#dashboard-topProducts-0 .card-text').html(topProductText);
                  showChart(sales.lineChart);
                  // List top products.
                  topProducts.sort((a,b)=>b.total-a.total);
                  for (let p of topProducts) {
                    $('#top-product tbody').append(`
                    <tr>
                      <td>${p.title}</td>
                      <td>${p.purchases}</td>
                      <td>${Number(p.total).toLocaleString('vi', {style : 'currency', currency : 'VND'})}</td>
                    </tr>
                  `);
                }

              }
            },
            error: function (jqXHR, textStatus, errorThrown) {
              console.info(textStatus);
            }
          });
        });

      });
    }
  };
})(jQuery, Drupal, once);
