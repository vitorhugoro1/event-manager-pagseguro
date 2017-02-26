Number.prototype.formatMoney = function(c, d, t) {
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
  var checked = $("[name=tipo]:checked").val();

  /**
   * Adiciona mascara nos campos a seguir
   */

  $("#ddd").mask('#0');
  $("#tel, #telefone").mask('0000-0000');

  if(checked == 'expositor'){
    $("#doc").mask('00.000.000/0000-00', {reverse: true});
  } else if (checked == 'visitante') {
    $("#doc").mask('000.000.000-00', {reverse: true});
  }

  /**
   * Adiciona um ingresso na lista de compra
   * @type {[type]}
   */

  $("#adc-ingresso").on('click', function() {
    var refID = $("#refID").val();
    var qtd = $("#qtd-ingresso").val();
    var tipo = $("#tipo-ingresso").val();
    var url = $("#action-url").val();
    var table = $("#table-form");

    if (qtd == "" || tipo == "") {
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
      beforeSend: function() {
        var $closing = jQuery('<div class="om-closing"></div>');
        jQuery('body').append($closing);
        $closing.fadeTo(400, .8);
        jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);
      },
      complete: function(data) {
        data = data.responseJSON;
        var body = table.children("tbody");
        var total = $("#total").val();
        var count = body.children("tr").length;
        var c = (count == 0) ? 0 : count + 1;
        var tr = '';
        console.log(data);
        if (1 == data.code) {
          tr += '<tr>';
          tr += '<td>';
          tr += '<input type="checkbox">';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" data-elem="tipo" name="ingressos[' + c + '][tipo]" value="' + data.return.tipo + '">';
          tr += '<span>' + data.return.label + '</span>';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" data-elem="qtd" name="ingressos[' + c + '][qtd]" value="' + data.return.qtd + '">';
          tr += '<span>' + data.return.qtd + '</span>';
          tr += '</td>';
          tr += '<td>';
          tr += '<input type="hidden" data-elem="valor" name="ingressos[' + c + '][valor]" value="' + data.return.valor + '">';
          tr += '<span>' + (data.return.valor).formatMoney(2, ',', '.') + '</span>';
          tr += '</td>';
          tr += '</tr>';

          var nTotal = parseFloat(total) + parseFloat(data.return.valor);
          $("#total").val(nTotal);
          $("#total-span").html(nTotal.formatMoney(2, ',', '.'));
          body.append(tr);
        }

        $("#qtd-ingresso").val(1);
        $("#tipo-ingresso").val("");

        jQuery('.om-closing').remove();
        jQuery('.om-loading-circle').remove();
      }
    });
  });

  /**
   * Remove um ingresso da lista de compra
   * @type {[type]}
   */

  $("#rmv-ingresso").on('click', function(event) {
    var table = $("#table-form");
    var selectit = table.children('tbody').find('input:checked');

    $.each(selectit, function(index, val) {
      var idx = $(val).parents('tr').index();
      var nxt = $(val).parents('tr').next().index();

      console.log($(val).parents('tr').next().is(':not(:checked)').index());
      console.log($(val).parents('tr').next().next().index());
      console.log($(val).parents('tr').next().next().next().index());
    });
  });

  /**
   * Finaliza a compra dos ingressos
   * @type {[type]}
   */

  $("#finalize").ajaxForm({
    beforeSubmit: function(arr, $form, options) {
      var $closing = jQuery('<div class="om-closing"></div>');
      jQuery('body').append($closing);
      $closing.fadeTo(400, .8);
      jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);
    },
    success: function(data) {
      jQuery('.om-closing').remove();
      jQuery('.om-loading-circle').remove();

      PagSeguroLightbox({
        code: data.code
      }, {
        success: function(transactionCode) {
          var $closing = jQuery('<div class="om-closing"></div>');
          jQuery('body').append($closing);
          $closing.fadeTo(400, .8);
          jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);

          $.post($('#finalize').attr('action'), {
            action: 'add_notification_code',
            orderID: data.orderID,
            transactionCode: transactionCode
          }, function(data) {
            console.log(data);
          });

          jQuery('.om-closing').remove();
          jQuery('.om-loading-circle').remove();
          window.location.href = $("#redirect_url").val();
        },
        abort: function() {
          var $closing = jQuery('<div class="om-closing"></div>');
          jQuery('body').append($closing);
          $closing.fadeTo(400, .8);
          jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);

          $.post($('#finalize').attr('action'), {
            action: 'add_abort_status',
            orderID: data.orderID
          }, function(data) {
            console.log(data);
          });

          jQuery('.om-closing').remove();
          jQuery('.om-loading-circle').remove();
          window.location.href = $("#redirect_url").val();
        }
      });
    }
  });

  /**
   * Atualiza o perfil do usuario
   * @type {[type]}
   */

  $('#update_perfil').ajaxForm({
    beforeSubmit: function(arr, $form, options) {
      var $closing = jQuery('<div class="om-closing"></div>');
      jQuery('body').append($closing);
      $closing.fadeTo(400, .8);
      jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);
    },
    success: function(data) {
      jQuery('.om-closing').remove();
      jQuery('.om-loading-circle').remove();

      if (data.success) {
        $("span[data-id='name']").text(data.data.name);
        $("span[data-id='email']").text(data.data.email);
        $("span[data-id='tel']").text(data.data.tel);
      }
    }
  });

  /**
   * Cria a paginação da tabela de ingressos
   * @type {Number}
   */

  $('table.paginated').each(function() {
    var currentPage = 0;
    var numPerPage = 7;
    var $table = $(this);
    $table.bind('repaginate', function() {
      $table.find('tbody tr').hide().slice(currentPage * numPerPage, (currentPage + 1) * numPerPage).show();
    });
    $table.trigger('repaginate');
    var numRows = $table.find('tbody tr').length;
    var numPages = Math.ceil(numRows / numPerPage);
    var $pager = $('<div class="pager"></div>');
    for (var page = 0; page < numPages; page++) {
      $('<span class="page-number"></span>').text(page + 1).bind('click', {
        newPage: page
      }, function(event) {
        currentPage = event.data['newPage'];
        $table.trigger('repaginate');
        $(this).addClass('active').siblings().removeClass('active');
      }).appendTo($pager).addClass('clickable');
    }
    $pager.insertAfter($table).find('span.page-number:first').addClass('active');
  });

  /**
   * Valida se um documento (CPF/CNPJ) está valido com o tipo
   * @type {[type]}
   */

  $("#doc").on({
    click: function(e) {
      var checked = $("[name=tipo]:checked").val();

      if(checked == 'expositor'){
        $("#doc").mask('00.000.000/0000-00', {reverse: true});
      } else if (checked == 'visitante') {
        $("#doc").mask('000.000.000-00', {reverse: true});
      } else {
        alert('Selecione um tipo');
      }

      e.preventDefault();
    }
  } );

  /**
   * Cadastrar usuario
   */

   $('#cadastrar_user').ajaxForm({
     beforeSubmit: function(arr, $form, options) {
       var $closing = jQuery('<div class="om-closing"></div>');
       jQuery('body').append($closing);
       $closing.fadeTo(400, .8);
       jQuery('<div class="om-loading-circle"></div>').appendTo('body').css('z-index', '100001').fadeIn(200);
     },
     success: function(data) {
       if(data.success){
         window.location = data.data.redirect;
       }
       console.log(data);
     }
   });

});
