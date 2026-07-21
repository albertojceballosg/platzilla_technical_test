<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Contains a variety of utility functions used to display UI
 * components such as form headers and footers.  Intended to be modified on a per
 * theme basis.
 ********************************************************************************/
global $app_strings,$theme,$CHECK_MOBILE,$plat;

if($theme=='modern' || $CHECK_MOBILE[0]){
    ?>
                </section><!-- /.right-side -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
    </body>
</html>
<?php
}elseif($plat=='pruebacrm' || $_SESSION['vtiger_authenticated_user_theme']=='centaurus'){
    ?>
    <!--end body panes-->
        </div>
    </div>

    <?php if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated']==1){ ?>
        <script type="text/javascript" src="modules/operating_modes/operating-modes.js"></script>
        <footer id="footer-bar" class="row" style="position: fixed; left: 237px; bottom: 0; width: calc(100% - 237px); z-index: 1;">
            <!-- wa 11-05-22- -->
			<p id="footer-copyright" class="col-xs-12">
                <span>&copy; 2004-<?=date('Y')?> <a href="http://www.gestionar-facil.com/que-es-platzilla/ " target="_blank">Platzilla.com</a></span> -
                <span><a href="/politica-de-privacidad.html" target="_blank">Política de privacidad</a></span> -
                <span><a href="/politica-de-cookies.html" target="_blank">Política de cookies</a></span> -
                <span><a href="/terminos-de-servicio.html" target="_blank">Términos de servicio</a></span>
            </p>
        </footer>
    <?php } else {
        include "Smarty/templates/centaurus/base/Footer.tpl";
    } ?>
    <script type="text/javascript" src="modules/operating_modes/operating-modes.js"></script>
    <script type="text/javascript">
        function doClick_platzi(vLocalizador){
            var obtener_elemento = document.querySelector(vLocalizador);
            obtener_elemento.click();
        }
    </script>
    </div>
    <div id="ascrail2001" class="nicescroll-rails" style="width: 3px; z-index: 1000; cursor: default; position: absolute; top: 74px; left: 237px; height: 341px; opacity: 0;"><div style="position: relative; top: 0px; float: right; width: 3px; height: 339px; background-color: rgb(31, 181, 173); border: 0px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 0px;"></div>
    </div>
    <div id="ascrail2001-hr" class="nicescroll-rails" style="height: 3px; z-index: 1000; top: 412px; left: 0px; position: absolute; cursor: default; display: none; width: 237px; opacity: 0;"><div style="position: relative; top: 0px; height: 3px; width: 240px; background-color: rgb(31, 181, 173); border: 0px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 0px;"></div>
    </div>
        </div>
            </div>
    <!-- </div>  </div> -->
    </body>
</html>
<?php
}else{
    ?>
    <!--end body panes-->
    </td></tr>
    <tr><td colspan="2" align="center">
    </td></tr></table>
</body>
</html>
<?php
}
?>