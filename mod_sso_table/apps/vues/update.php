<?php
/*
* $Id: update.php 7744 2011-08-14 13:07:15Z dblanqui $
*
* Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
*
* This file is part of GEPI.
*
* GEPI is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* GEPI is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GEPI; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// On emp�che l'acc�s direct au fichier
if (basename($_SERVER["SCRIPT_NAME"])==basename(__File__)){
    die();
};
?>
[onload;file=menu.php]
<p>Update of the user : [b1.login_gepi] </p>
<p class='message_red'> If the fields login sso is not empty it is that a correspondence
exists d�ja for this user. Be certain update</p>
<form action="index.php?ctrl=maj&action=updated"  method="post">
      <p>Login g�pi :</p>
       <input type="text" name="login_gepi" id="login_gepi" value="[b1.login_gepi]" disabled />
       <input type="hidden" name="login_gepi" id="login_gepi" value="[b1.login_gepi]" />
       <p>Login sso :</p>    <input type="text" name="login_sso" id="login_sso" value="[b1.login_sso;if b1.login_ss0 !='']" />
          <input type="submit" name="action" value="Mise &agrave; jour" class="submit"/>
</form>
</body>
 </html>