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

$("#sortie_ville").change(function(){
    var villeSelec = $(this).val();
    var ville = $(this);

/*    console.log($('#sortie_ville').val());
    console.log(ville.find("option:selected").text());*/

//récupérer val ville dans l'url dans queryString

    $.ajax({
       url: 'lieux',
       type:       'GET',
       dataType:   'JSON',
       data : {villeid:villeSelec},
       async:      true,

       success: function (lieux){

           var lieuSelec = $("#sortie_lieu");

           lieuSelec.html('');

           lieuSelec.append('<option value> Selectionner lieu de ' + ville.find("option:selected").text() + ' ...</option>');

            $.each(lieux, function (key,lieu) {
                lieuSelec.append('<option value="' + lieu.id + '">' + lieu.nom + '</option>');
                console.log("dddd :"+key + " " + lieu.id);
            });

        },
        error : function(error) {
            alert('Une erreur est survenue :( ');
        }
    });
});

$("#sortie_lieu").change(function(){
    var lieuSelec = $(this).val();
    console.log(lieuSelec);

    $.ajax({
        url: 'lieu',
        type: 'GET',
        dataType: 'JSON',
        data: {lieuid: lieuSelec},
        async: true,

        success: function (lieu) {
            var element = document.getElementById("rue");
            element.removeChild(element.childNodes[0]);
            var rue = document.createElement("dt");

            var text = document.createTextNode("Rue : " + lieu.rue);
            rue.appendChild(text);
            element.appendChild(rue);

            if (lieu.latitude){
                var divLat = document.getElementById("lati");
                divLat.removeChild(divLat.childNodes[0]);
                var lat = document.createElement("dt");

                var latText = document.createTextNode("Latitude : " + lieu.latitude);
                lat.appendChild(latText);
                divLat.appendChild(lat);


                var divLongi = document.getElementById("longi");
                divLongi.removeChild(divLongi.childNodes[0]);
                var longi = document.createElement("dt");

                var longiText = document.createTextNode("Longitude : " + lieu.longitude);
                longi.appendChild(longiText);
                divLongi.appendChild(longi);
            }else{
                var divLat = document.getElementById("lati");
                divLat.removeChild(divLat.childNodes[0]);
                var lat = document.createElement("dt");

                var latText = document.createTextNode("Latitude : Non renseignée");
                lat.appendChild(latText);
                divLat.appendChild(lat);


                var divLongi = document.getElementById("longi");
                divLongi.removeChild(divLongi.childNodes[0]);
                var longi = document.createElement("dt");

                var longiText = document.createTextNode("Longitude : Non renseignée");
                longi.appendChild(longiText);
                divLongi.appendChild(longi);
            }
        },
            error : function(error) {
                alert('Une erreur est survenue :( ');
            }

    });
});



