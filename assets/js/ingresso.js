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


function closeBoxIngresso(){
  self.parent.tb_remove();
}

function addIngresso(){
  var $add = '';
  var $tipo = jQuery("#tipo-ingresso").val();
  var $qtd = jQuery("#qtd-ingresso").val();
  var $valor = jQuery("#valor-ingresso").val();
  var $last_id = jQuery('.ingresso-table tbody tr:last-of-type').data('id');

  jQuery('.ingresso-table tbody').children('tr.none').remove();

  if($tipo == ''){
    alert('Selecione um tipo');
    jQuery("#tipo-ingresso").focus();
    return;
  }
  if($qtd < 1){
    alert('Coloque a quantidade');
    jQuery("#qtd-ingresso").focus();
    return;
  }

  if($last_id != undefined && $last_id != ''){
    $add = '<tr data-id="' + ($last_id + 1)  + '">';
    $add += '<td>'+
              '<input type="checkbox" data-id="remover" value="' + ($last_id + 1)  + '">' +
            '</td>';
    $add += '<td><input type="hidden" name="ingressos[' + ($last_id + 1)  + '][tipo]" value="' + $tipo  + '">' +
              jQuery("#tipo-ingresso option:selected").text()  +
            '</td>' +
            '<td><input type="hidden" name="ingressos[' + ($last_id + 1)  + '][qtd]" value="' + $qtd  + '">' +
              $qtd +
            '</td>' +
            '<td><input type="hidden" name="ingressos[' + ($last_id + 1)  + '][valor]" value="' + ($valor * $qtd)  + '">' +
              'R$ ' +  ($valor * $qtd).formatMoney(2, ',', '.') +
            '</td>';
    $add += '</tr>';

    jQuery('.ingresso-table tbody').append($add);
  } else {
    $add = '<tr data-id="' + 0  + '">';
    $add += '<td>'+
              '<input type="checkbox" data-id="remover" value="' + 0  + '">' +
            '</td>';
    $add += '<td><input type="hidden" name="ingressos[' + 0  + '][tipo]" value="' + $tipo  + '">' +
              jQuery("#tipo-ingresso option:selected").text()  +
            '</td>' +
            '<td><input type="hidden" name="ingressos[' + 0  + '][qtd]" value="' + $qtd  + '">' +
              $qtd +
            '</td>' +
            '<td><input type="hidden" name="ingressos[' + 0  + '][valor]" value="' + ($valor * $qtd)  + '">' +
              'R$ ' +  ($valor * $qtd).formatMoney(2, ',', '.') +
            '</td>';
    $add += '</tr>';

    jQuery('.ingresso-table tbody').append($add);
  }

  calculaTotal();

  jQuery("#tipo-ingresso").val("").trigger("change");
  jQuery("#qtd-ingresso").val("");
  self.parent.tb_remove();
}

function removerIngresso(){
  var $empty = '<tr class="none">' +
    '<td>' +
      '<p>' +
        'Nenhum ingresso' +
      '</p>' +
    '</td>' +
  '</tr>';

  jQuery("input[data-id='remover']:checked").each(function(i,v){
    jQuery(v).parents('tr').remove();
  });
  calculaTotal();

  if(jQuery('.ingresso-table tbody tr').length == 0){
    jQuery('.ingresso-table tbody').append($empty);
  }

  jQuery(this).attr('disabled', true);
}

function calculaTotal(){
  var $sum = 0;

  jQuery(".ingresso-table tbody").find("tr td:last-of-type input").each(function(i,v){
    $sum += parseFloat(v.value);
  });

  jQuery("#ingressos-total").val($sum.formatMoney(2, '.', ''));
  var clone = jQuery("#ingressos-total").clone();
  jQuery("#ingressos-total").parent().text("R$ " + $sum.formatMoney(2, ",", ".")).append(clone);
}

jQuery(document).ready(function($) {
  $('#tipo-ingresso').select2();
  $("#cancel-ingresso").click(closeBoxIngresso);
  $("#add-ingresso").click(addIngresso);
  $(".remover-ingresso").click(removerIngresso);

  $('#tipo-ingresso').on('change', function(){
    var parse = $.parseJSON($("#valores-json").val());
    var id = $(this).prop('id');

    if($(this).val() != ''){
      idx = $(this).val();
      valor = parse[idx].valor.replace(",", ".");
      jQuery("#valor-ingresso").val(valor);
    }
  });

  $(document).on('change', "input[data-id='remover']", function(e){
    e.preventDefault();

    if($(this).is(':checked')){
      $(".remover-ingresso").attr("disabled", false);
    } else {
      if($("input[data-id='remover']:checked").length == 0){
        $(".remover-ingresso").attr('disabled', true);
      }
    }
  });

});
