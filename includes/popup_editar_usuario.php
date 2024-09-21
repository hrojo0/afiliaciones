  <div class="modificar_usuario" id="modificar_usuario" > 
                <div class="cont_modificar" style="background:#fff">
                <div id="panel_mod" class="panel_mod">
                <i class="fa-solid fa-circle-xmark" id="close_info_mod"></i>
                <form class="modificarUsuario" id="modificarUsuario" name="modificarUsuario" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                   <div class="ajuste_adduser">
                       <div class="row_mod" id="row_mod">
                            <!--div class="cont_preview"-->
                                <div class="file-preview_mod" id="file-preview_mod">
                                    <img src="" alt="" id="file_pr_mod">
                                </div>
                            <!--/div-->
                            <label id="foto-label_mod" for="file-input_mod">
                               <div class="over_label_mod">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            </label>
                            <input id="file-input_mod" type="file" name="file" accept="image/*" style="display:none">

                       </div>


                        <div id="datos_usuario_mod">
                           <input type="text" class="form__input" placeholder=" " name="usuario" id="usuario_mod" value="<?php echo $usr_usuario; ?>" style="display:none">
                           <input type="text" class="form__input" placeholder=" " name="user" id="user_mod" value="<?php echo $usr_user; ?>" style="display:none">
                           <input type="text" class="form__input" placeholder=" " name="nivel_usuario" id="user_nivel_mod" value="<?php echo $usr_nivel; ?>" style="display:none">
                           
                           
                            <div class="form__div" id="form__div__nombre_mod">
                                <input type="text" class="form__input" placeholder=" " name="nombre" id="nombre_mod" value="">
                                <label for="" class="form__label">Nombre*</label>
                            </div>

                            <div class="form__div" id="form__div__aps_mod">
                                <input type="text" class="form__input" placeholder=" " name="apellidos" id="aps_mod" value="">
                                <label for="" class="form__label">Apellidos*</label>
                            </div>

                            <div id="change_pass_mod" class="">
                                <p>Cambiar contraseña</p>

                            </div>
                            <input style="display: none" type="number" class="form__input" placeholder=" " name="flag_pass" id="pass_flag_mod" value="0" readonly>
                            <input style="display: none" type="number" class="form__input" placeholder=" " name="flag_user_mod" id="pass_user_mod" value="1" readonly>

                            <div class="form__div" id="form__div__oldpass_mod">
                                <input type="password" class="form__input" placeholder=" " name="old_pass" id="old_pass_mod" value="">
                                <label for="" class="form__label">Contraseña actual*</label>
                            </div>

                            <div class="form__div" id="form__div__newpass_mod">
                                <input type="password" class="form__input" placeholder=" " name="new_pass" id="new_pass_mod" value="">
                                <label for="" class="form__label">Nueva contraseña*</label>
                            </div>

                            <div class="form__div" id="form__div__newpasscheck_mod">
                                <input type="password" class="form__input" placeholder=" " name="pass_check" id="pass_check_mod" value="">
                                <label for="" class="form__label">Confirmar nueva contraseña*</label>
                            </div>   

                        </div>
                    </div>
                    <div class="btns_persona_mod">
                        
                        <button id="submit_mod" type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                        
                        <div id="delete_persona_mod" class="form__button">
                            <div id="cancel_mod">
                                 <i class="fa-solid fa-xmark"></i>

                                <p class="submit-txt">Cancelar</p>
                            </div>
                            
                        </div>                        
                        
                    </div>
                    
                </form>
           </div>
               </div>
            </div>