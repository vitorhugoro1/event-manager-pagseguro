function closeBoxIngresso(){
  self.parent.tb_remove();
}

function parseTable(table) {
	var parse = {};
  var c = 0;

  table.find('input, select').each(function(i, v){
    id = jQuery(v).prop('id');
    switch(id){
      case 'tipo-ingresso':
      case 'qtd-ingresso':
        if(jQuery(v).val() !== ''){
          parse[id] = jQuery(v).val();
          c++;
        }
        break;
    }
  });

  return parse;
}

function addIngresso(){
  var form = jQuery('#ingresso-option-form');
  var parse = {};

  parse = parseTable(form);

  if(parse.length > 1){
    jQuery('.ingresso-table tbody').children('tr.none').remove();

    
  }
}

jQuery(document).ready(function($) {
  $('#tipo-ingresso').select2();
  $("#cancel-ingresso").click(closeBoxIngresso);
  $("#add-ingresso").click(addIngresso);

});
