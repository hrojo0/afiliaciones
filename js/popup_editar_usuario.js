 /*Carga popup con info usuario*/
$('#modificar_usuario').hide();
$('#img_perfil').click(function(){
    //$('.borrado_success').remove();
    //$('.form__success').remove();
    //$('#info_persona').fadeIn(300);

    $('#form__div__oldpass_mod').hide();
    $('#form__div__newpass_mod').hide();
    $('#form__div__newpasscheck_mod').hide();
    $('#modificar_usuario').fadeIn(300);
    $('#change_pass_mod').show();

    usuario = $('.header_datos_persona')[0].id;
    $('.cont_modificar')[0].id = usuario;


    $.post("ajax/usuario_unico.php",{ usuario: usuario}, function(data){
        foto = "";
        if(data == ""){
           //console.log('Error');
        }else{
            json = jQuery.parseJSON(data);
            if(json['foto'] == 0){ foto = 'img/user.png'; } else { foto = json['foto']}
            $('#file_pr_mod').attr('src',foto);
            $('#nombre_mod').val(json['nombre']);
            $('#aps_mod').val(json['apellidos']);
        }
    });
});


//cierra popup
$('#close_info_mod, #delete_persona_mod').click(function(){
    $('#modificar_usuario').fadeOut(300);
    $('#old_pass_mod').val('');
    $('#new_pass_mod').val('');
    $('#pass_check_mod').val('');
    $('#pass_flag').val("0");
    /*if(!$('#delete_default').is(':visible')){
        $('#delete_default, #delete_confirmar').toggle(300);
    }
    $('.form__error').remove();*/
});

/*Mostrar campos de contraseña*/
//toggle cambiar pass
$('#change_pass_mod').click(function(){
    $('#change_pass_mod').hide();
    $('#pass_flag_mod').val("1");
    $('#form__div__oldpass_mod').fadeIn(200);
    $('#form__div__newpass_mod').fadeIn(200);
    $('#form__div__newpasscheck_mod').fadeIn(200);
});

/* Guarda modificacion perfil*/
$("#modificarUsuario").submit(function(e){
    e.preventDefault();
    usuario = $('.cont_modificar')[0].id;

    nombre = $('#nombre_mod').val();
    apellidos = $('#aps_mod').val();
    old_pass = $('#old_pass_mod').val();
    new_pass = $('#new_pass_mod').val();
    pass_check = $('#pass_check_mod').val();
    flag_pass = $('#pass_flag_mod').val();
    user = $('.header_user_info')[0].id;
    nivel_usuario = $('.flex_header_user')[0].id;

    $.post("ajax/editar-usuario.php", {usuario: usuario, nombre: nombre, apellidos: apellidos, user: user, flag_pass: flag_pass, old_pass: old_pass, new_pass: new_pass, pass_check: pass_check, nivel_usuario: nivel_usuario}, function(data){
        console.log(data);
        if(data != "200"){
            $('#success_update').remove();
            respuesta = jQuery.parseJSON(data);
            $('.form__error').remove();
            marcaErrores(respuesta['e_nom'], $('#form__div__nombre_mod'), ' nombre(s)');
            marcaErrores(respuesta['e_aps'], $('#form__div__aps_mod'), 'apellidos');
            marcaErrores(respuesta['e_new_pass'], $('#form__div__newpass_mod'), 'contraseña');
            marcaErrores(respuesta['e_old_pass'], $('#form__div__oldpass_mod'), 'contraseña');
            marcaErrores(respuesta['e_new_pass_check'], $('#form__div__newpasscheck_mod'), 'contraseña');
        } else {
            $('#error_update').remove();
            $('#datos_usuario_mod').append('<div class="form__success respuesta_popup" id="success_update_mod"><i class="fa-sharp fa-solid fa-thumbs-up success"></i> <p class="success post"> Datos actualizados</p></div>');
            $('#success_update_mod').hide();
            $('#success_update_mod').fadeIn(300);
            $('#modificar_usuario').delay(1500).fadeOut(300);
            $('#old_pass_mod').val('');
            $('#new_pass_mod').val('');
            $('#pass_check_mod').val('');
            cargaUsuarios(1,$('.active')[1].id);
        }
    });

            
});



//cargar thumbnail de foto
 $('#file-input_mod').change(function(e) {
    if(document.getElementById("file-input_mod").files.length != 0 ){
    if(typeof FileReader == "undefined") return true;
        $('#foto-label_mod').html("");

    var elem = $(this);
    var files = e.target.files;

    for (var i = 0, f; f = files[i]; i++) {
        if (f.type.match('image.*')) {
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var image = e.target.result;
                    $('#file_pr_mod').attr('src',image);
                    $('#row_mod').attr('style','background:none');
                    $('#foto-label_mod').html("");
                };
            })(f);
            reader.readAsDataURL(f);
        }
    }} else {
        $('#file_pr_mod').attr('src','');
        $('#row_mod').attr('style','background:#c9c9c9');
        $('#foto-label_mod').html('<div class="over_label_mod"><i class="fa-solid fa-image"></i></div>');
    }
});