(function ($, Drupal, drupalSettings, once) {
  function priceFormatter(data) {
    var field = this.field
    return '$' + data.map(function (row) {
      return +row[field]
    }).reduce(function (sum, i) {
      return sum + i
    }, 0)
  }
  Drupal.behaviors.total = {
    attach: function (context, settings) {
      $(once('total_js', '#block-phothin1955-content', context)).each(function () {
        let b = [];
        let c = [];
        let url = window.location.href;
        let search = url.split('?')[1];
        $.getJSON('/all-orders-item?_format=json&' + `${search}`, function (data) {
          const productSet = new Set(b.map(p => p.id_product));
          const userSet = new Set(c.map(u => u.uid));

          data.forEach(function (value) {
            if (!productSet.has(value.idProduct)) {
              if(value.idProduct != "") {
                b.push({ 'id_product': value.idProduct, 'name_product': value.nameProduct });
                productSet.add(value.idProduct);
              }
            }
            if (!userSet.has(value.id)) {
              c.push({ 'uid': value.id, 'name_user': value.nameUser });
              userSet.add(value.id);
            }
          });

          b.forEach(value => {
            $('#table thead tr').append(`<th data-field="name_product_${value.id_product}">${value.name_product}</th>`);
          });
          const resData = c.map(function (elementUser) {
            const row = {};
            row.name_user = elementUser.name_user;

            b.forEach(function (elementProduct) {
              let thisData = data.filter(element => element.id == elementUser.uid && element.idProduct == elementProduct.id_product);
              let quantity = thisData.reduce((acc, item) => acc + Number(item.quantity), 0);
              row["name_product_" + elementProduct.id_product] = quantity;
            });

            const thisData2 = data.filter(element => element.id == elementUser.uid);
            let totalPrice = thisData2.reduce((acc, item) => acc + Number(item.totalPrice), 0);
            row["total_price"] = totalPrice;

            let totalQuantity =  thisData2.reduce((acc, item) => acc + Number(item.quantity), 0);
            row["total_quantity"] = totalQuantity;
            return row;
          });

          resData.sort((a, b) => b.total_price - a.total_price);
          $('#table thead tr').append(`<th data-field="total_quantity">Tổng số lượng sản phẩm</th>`);
          $('#table thead tr').append(`<th data-field="total_price">Tổng giá trị các đơn hàng</th>`);
          $('#table').bootstrapTable({ data: resData });
          $('#table').each(function (i, table) {
            calculateColumn(table);
          });
        })
      })
    }
  }
  function calculateColumn(indexTable) {
    $(indexTable).find('tr th').each(function (index) {
      var total = 0;
      $(indexTable).find('tr').each(function () {
        var value = parseInt($('td', this).eq(index + 1).text());
        if (!isNaN(value)) {
          total += value;
        }
      });
      if (total) {
        $(indexTable).find('tfoot th .th-inner').eq(index + 1).text(total);
      }
    })
  }
  $(function () {
    $('#toolbar').find('select').change(function () {
      $('#table').bootstrapTable('destroy').bootstrapTable({
        exportDataType: $(this).val(),
        exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
      })
    }).trigger('change')
  })
}(jQuery, Drupal, drupalSettings, once));
