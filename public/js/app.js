/*var $sortie_ville = $('sortie_ville')

$sortie_ville.change(function ()
{
    var $form = $(this).closest('form')

    var data = {}

    data[$sortie_ville.attr('name')] = $sortie_ville.val()

    $.post($form.attr('action'), data).then(function(response)
    {
        $('#sortie_lieu').replaceWith(
            $(response).find('#sortie_lieu')
        )
    })
})*/


$(document).on('change','#sortie_ville','#sortie_lieu', function(){
    let $field =$(this)
    let $villeField = $('#sortie_ville')
    let $form = $field.closest('form')
    let data = {}
    data[$villeField.attr('name')] = $villeField.val()

// Submit data via AJAX to the form's action path.
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            // Replace current race field ...
            $('#sortie_lieu').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#sortie_lieu')
            );
            // race field now displays the appropriate positions.
        }
    });
})


/*$(document).on('change','#sortie_ville','#sortie_lieu', function(){
    let $field =$(this)
    let $villeField = $('#sortie_ville')
    let $form = $field.closest('form')
    let data = {}
    data[$villeField.attr('name')] = $villeField.val()
    $.post($form.attr('action'), data).then(function(data) {
        let $input = $(data).find('#sortie_lieu')
        $('#sortie_lieu').replaceWith($input)
    })
})*/


