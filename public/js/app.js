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







/*$(document).on('change','#sortie_ville','#sortie_lieu', function(){
// Submit data via AJAX to the form's action path.
    $.ajax({
        url: 'sortie/createSortie',
        type:       'POST',
        dataType:   'json',
        async:      true,
        success:
            function(data, status) {
                var e = $('<tr><th>Name</th><th>Address</th></tr>');
                $('#sortie_ville').html('');
                $('#student').append(e);

                for(i = 0; i < data.length; i++) {
                    student = data[i];
                    var e = $('<tr><td id = "name"></td><td id = "address"></td></tr>');

                    $('#name', e).html(student['name']);
                    $('#address', e).html(student['address']);
                    $('#student').append(e);
                }
            },
        error : function(xhr, textStatus, errorThrown) {
            alert('Ajax request failed.');
        }
    });
})*/

$(document).ready( function(){
// Submit data via AJAX to the form's action path.
    $("#sortie_lieu").on('click', function(event){
        $.ajax({
            url: 'sortie/createSortie',
            type:       'GET',
            dataType:   'JSON',
            async:      true,
            success:
                function(data,status) {
                var e = $('');
                    $('#sortie_lieu').html('');
                    $('#sortie_lieu').append(e);

                   for(i = 0; i < data.length; i++) {

                    $('#',e).html(lieu['rue']);
                    $('#',e).html(lieu['latitude']);
                    $('#',e).html(lieu['longitude']);
                    $('#sortie_lieu',e).append(e);
                   }
                },
            error : function(xhr, textStatus, errorThrown) {
                alert('Une erreur est survenue :( ');
            }
        })

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


