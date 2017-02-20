Number.prototype.formatMoney = function(c, d, t){
var n = this,
    c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };


jQuery(document).ready(function($) {
  $("#adc-ingresso").on('click', function(){
    var refID = $("#refID").val();
    var qtd = $("#qtd-ingresso").val();
    var tipo = $("#tipo-ingresso").val();
    var url = $("#action-url").val();
    var table = $("#table-form");

    if(qtd == "" || tipo == ""){
      alert("Preencha todos os campos");
      return false;
    }

    $.ajax({
      method: "POST",
      url: url + '?action=validation_ingresso',
      data: {
        refID: refID,
        id: tipo,
        qtd: qtd
      },
      type: "jsonp",
      complete: function(data){
        data = data.responseJSON;
        var body = table.children("tbody");
        var total = $("#total").val();
        var count = body.children("tr").length;
        var c = (count == 0) ? 0 : count + 1;
        var tr = '';

        if(1 == data.code){
          tr += '<tr>';
          tr += '<td>';
          tr += '<input type="checkbox">';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" name="ingresso[' + c + '][tipo]" value="' + data.return.tipo + '">';
          tr += '<span>' + data.return.label + '</span>';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" name="ingresso[' + c + '][qtd]" value="' + data.return.qtd + '">';
          tr += '<span>' + data.return.qtd + '</span>';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" name="ingresso[' + c + '][valor]" value="' + data.return.valor + '">';
          tr += '<span>' + (data.return.valor).formatMoney(2, ',', '.') + '</span>';
          tr += '</td>';
          tr += '</tr>';

          var nTotal = parseFloat(total) + parseFloat(data.return.valor);
          $("#total").val(nTotal);
          $("#total-span").html(nTotal.formatMoney(2, ',', '.'));
          body.append(tr);
        }
      }
    });
  });
});
