<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

if ($autoreply_id = $_REQUEST['autoreply_id']) {
	if (! ($autoreply_id = dba_valid(_DB_PREF_.'_featureAutoreply', 'autoreply_id', $autoreply_id))) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_autoreply_list":
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			"._button('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_add', _('Add SMS autoreply'));
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=70%>"._('Keyword')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=90%>"._('Keyword')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "</tr></thead><tbody>";
		if (! auth_isadmin()) {
			$query_user_only = "WHERE uid='".$user_config['uid']."'";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply ".$query_user_only." ORDER BY autoreply_keyword";
		$db_result = dba_query($db_query);
		$i=0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$action = "<a href=\""._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$db_row['autoreply_id'])."\">".$icon_config['manage']."</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS autoreply ?')." ("._('keyword').": ".$db_row['autoreply_keyword'].")','"._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_del&autoreply_id='.$db_row['autoreply_id'])."')\">".$icon_config['delete']."</a>";
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>".$db_row['autoreply_keyword']."</td>
						".$option_owner."
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			"._button('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_add', _('Add SMS autoreply'));
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "sms_autoreply_manage":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$manage_autoreply_keyword = $db_row['autoreply_keyword'];
		$o_uid = $db_row['uid'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<p>"._('SMS autoreply keyword').": ".$manage_autoreply_keyword."</p>
			"._button('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id='.$autoreply_id, _('Add SMS autoreply scenario'))."
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>"._('Param')."</th>
				<th width=50%>"._('Return')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=20%>"._('Param')."</th>
				<th width=70%>"._('Return')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "</tr></thead><tbody>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' ORDER BY autoreply_scenario_param1";
		$db_result = dba_query($db_query);
		$j=0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($o_uid)) {
				$list_of_param = "";
				for ($i=1;$i<=7;$i++) {
					$list_of_param .= $db_row['autoreply_scenario_param'.$i]."&nbsp;";
				}
				$action = "<a href=\""._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id='.$autoreply_id.'&autoreply_scenario_id='.$db_row['autoreply_scenario_id'])."\">".$icon_config['edit']."</a>";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this SMS autoreply scenario ?')."','"._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_del&autoreply_id='.$autoreply_id.'&autoreply_scenario_id='.$db_row['autoreply_scenario_id'])."')\">".$icon_config['delete']."</a>";
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$j++;
				$content .= "
					<tr>
						<td>$list_of_param</td>
						<td align=left>".$db_row['autoreply_scenario_result']."</td>
						".$option_owner."
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			</form>
			"._button('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id='.$autoreply_id, _('Add SMS autoreply scenario'))."
			"._back('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_list');
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "sms_autoreply_del":
		$db_query = "SELECT autoreply_keyword FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($keyword_name = $db_row['autoreply_keyword']) {
			$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_keyword='$keyword_name'";
			if (@dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id'";
				dba_query($db_query);
				$_SESSION['error_string'] = _('SMS autoreply has been deleted')." ("._('keyword').": $keyword_name)";
			} else {
				$_SESSION['error_string'] = _('Fail to delete SMS autoreply')." ("._('keyword').": $keyword_name";
			}
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_list'));
		exit();
		break;
	case "sms_autoreply_add":
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Add SMS autoreply')."</h3>
			<form action=index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_add_yes method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('SMS autoreply keyword')."</td>
					<td><input type=text size=10 maxlength=10 name=add_autoreply_keyword value=\"$add_autoreply_keyword\"></td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value='"._('Save')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_list');
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "sms_autoreply_add_yes":
		$add_autoreply_keyword = trim(strtoupper($_POST['add_autoreply_keyword']));
		if ($add_autoreply_keyword) {
			if (checkavailablekeyword($add_autoreply_keyword)) {
				$db_query = "INSERT INTO "._DB_PREF_."_featureAutoreply (uid,autoreply_keyword) VALUES ('".$user_config['uid']."','$add_autoreply_keyword')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS autoreply keyword has been added')." ("._('keyword').": $add_autoreply_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS autoreply')." ("._('keyword').": $add_autoreply_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS keyword already exists, reserved or use by other feature')." ("._('keyword').": $add_autoreply_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_add'));
		exit();
		break;
		// scenario
	case "sms_autoreply_scenario_del":
		$_SESSION['error_string'] = _('Fail to delete SMS autoreply scenario');
		if ($autoreply_id && ($autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'])) {
			$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been deleted');
			}
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$autoreply_id));
		exit();
		break;
	case "sms_autoreply_scenario_add":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autoreply_keyword = $db_row['autoreply_keyword'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Add SMS autoreply scenario')."</h3>
			<form action=index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add_yes method=post>
			"._CSRF_FORM_."
			<input type=hidden name=autoreply_id value=\"$autoreply_id\">
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('SMS autoreply keyword')."</td><td>".$autoreply_keyword."</td>
				</tr>";
		for ($i=1;$i<=7;$i++) {
			$content .= "
				<tr>
					<td>"._('SMS autoreply scenario parameter')." $i</td><td><input type=text maxlength=20 name=\"add_autoreply_scenario_param".$i."\" value=\"".${"add_autoreply_scenario_param".$i}."\">\n</td>
				</tr>";
		}
		$content .= "
			<tr>
				<td>"._('SMS autoreply scenario replies with')."</td><td><input type=text name=add_autoreply_scenario_result value=\"$add_autoreply_scenario_result\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value='"._('Save')."'>
			</form>
			"._back('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$autoreply_id);
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "sms_autoreply_scenario_add_yes":
		$add_autoreply_scenario_result = $_POST['add_autoreply_scenario_result'];
		for ($i=1;$i<=7;$i++) {
			${"add_autoreply_scenario_param".$i} = trim(strtoupper($_POST['add_autoreply_scenario_param'.$i]));
		}
		if ($add_autoreply_scenario_result) {
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_param_list .= "autoreply_scenario_param$i,";
			}
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_keyword_param_entry .= "'".${"add_autoreply_scenario_param".$i}."',";
			}
			$db_query = "
				INSERT INTO "._DB_PREF_."_featureAutoreply_scenario 
				(autoreply_id,".$autoreply_scenario_param_list."autoreply_scenario_result) VALUES ('$autoreply_id',$autoreply_scenario_keyword_param_entry'$add_autoreply_scenario_result')";
			if ($new_uid = dba_insert_id($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been added');
			} else {
				$_SESSION['error_string'] = _('Fail to add SMS autoreply scenario');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id='.$autoreply_id));
		exit();
		break;
	case "sms_autoreply_scenario_edit":
		$autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'];
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autoreply_keyword = $db_row['autoreply_keyword'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Edit SMS autoreply scenario')."</h3>
			<form action=index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit_yes method=post>
			"._CSRF_FORM_."
			<input type=hidden name=autoreply_id value=\"$autoreply_id\">
			<input type=hidden name=autoreply_scenario_id value=\"$autoreply_scenario_id\">
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('SMS autoreply keyword')."</td><td>".$autoreply_keyword."</td>
				</tr>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		for ($i=1;$i<=7;$i++) {
			${"edit_autoreply_scenario_param".$i} = $db_row['autoreply_scenario_param'.$i];
		}
		for ($i=1;$i<=7;$i++) {
			$content .= "
				<tr>
					<td>"._('SMS autoreply scenario parameter')." $i</td><td><input type=text maxlength=20 name=edit_autoreply_scenario_param$i value=\"".${"edit_autoreply_scenario_param".$i}."\">\n</td>
				</tr>";
		}
		$edit_autoreply_scenario_result = $db_row['autoreply_scenario_result'];
		$content .= "
			<tr>
				<td>"._('SMS autoreply scenario replies with')."</td><td><input type=text name=edit_autoreply_scenario_result value=\"$edit_autoreply_scenario_result\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\"></p>
			</form>
			"._back('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$autoreply_id);
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "sms_autoreply_scenario_edit_yes":
		$autoreply_scenario_id = $_POST['autoreply_scenario_id'];
		$edit_autoreply_scenario_result = $_POST['edit_autoreply_scenario_result'];
		for ($i=1;$i<=7;$i++) {
			${"edit_autoreply_scenario_param".$i} = trim(strtoupper($_POST['edit_autoreply_scenario_param'.$i]));
		}
		if ($edit_autoreply_scenario_result) {
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_param_list .= "autoreply_scenario_param".$i."='".${"edit_autoreply_scenario_param".$i}."',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_featureAutoreply_scenario 
				SET c_timestamp='".mktime()."',".$autoreply_scenario_param_list."autoreply_scenario_result='$edit_autoreply_scenario_result' 
				WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
			if ($db_result = @dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been edited');
			} else {
				$_SESSION['error_string'] = _('Fail to edit SMS autoreply scenario');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id='.$autoreply_id.'&autoreply_scenario_id='.$autoreply_scenario_id));
		exit();
		break;
}

?>