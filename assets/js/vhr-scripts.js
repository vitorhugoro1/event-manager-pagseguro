jQuery(document).ready(function($) {
  $(".cmb-type-text-money .cmb2-text-money").each(function() {
    $(this).mask("000.000.000.000.000,00", {
      reverse: true,
      placeholder: '0.000,00'
    });
  });

  /**
   * Add mask for new input, with timeout for fix the start time error
   * @author Vitor H Rodrigues
   */

  $("button[data-selector='_vhr_valores_repeat'].cmb-add-group-row").on('click', setTimeout(
    function() {
      $(".cmb-type-text-money .cmb2-text-money").each(function() {
        $(this).mask("000.000.000.000.000,00", {
          reverse: true,
          placeholder: '0.000,00'
        });
      });
    },
    2000
  ));


});
