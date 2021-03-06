<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/minigolf.lib.php
 *	\ingroup	minigolf
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function minigolfAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("minigolf@minigolf");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/minigolf/admin/minigolf_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/minigolf/admin/minigolf_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'minigolf');

    return $head;
}


function minigolfPrepareHeadForParcoursCard($id)
{
    global $langs, $conf;

    $langs->load("minigolf@minigolf");

    $h = 0;
    $head = array();


    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to remove a tab


    $head[$h][0] = dol_buildpath("/minigolf/cardParcours.php?action=edit&id=", 1) .$id; //link
    $head[$h][1] = $langs->trans("Editer parcours"); // label
    $head[$h][2] = 'tabParcours'; //id link
    $h++;
    $head[$h][0] = dol_buildpath("/minigolf/listParcoursTrou.php?action=edit&parcoursId=", 1).$id;
    $head[$h][1] = $langs->trans("Editer les trous associés");
    $head[$h][2] = 'tabTrous';
    $h++;


   // complete_head_from_modules($conf, $langs, $object, $head, $h, 'minigolf');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Tminigolf	$object		Object company shown
 * @return 	array				Array of tabs
 */
function minigolf_prepare_head( $object) // typage enlevé, car pas utilisé (pour le moment)
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/minigolf/card.php', 1).'?id='.$object->getId();
    $head[$h][1] = $langs->trans("minigolfCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'minigolf');
	
	return $head;
}

function getFormConfirm(&$PDOdb, &$form, &$object, $action)
{
    global $langs,$conf,$user;

    $formconfirm = '';

    if ($action == 'validate' && !empty($user->rights->minigolf->write))
    {
        $text = $langs->trans('ConfirmValidateminigolf', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Validateminigolf'), $text, 'confirm_validate', '', 0, 1);
    }
    elseif ($action == 'delete' && !empty($user->rights->minigolf->write))
    {
        $text = $langs->trans('ConfirmDeleteminigolf');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Deleteminigolf'), $text, 'confirm_delete', '', 0, 1);
    }
    elseif ($action == 'clone' && !empty($user->rights->minigolf->write))
    {
        $text = $langs->trans('ConfirmCloneminigolf', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('Cloneminigolf'), $text, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}



function _getTrouNameFromId($id)
{

    $PDOdb = new TPDOdb;

    $obj = new TTrou;

    $obj->load($PDOdb, $id);

    return $obj->name;

}


function _getUserNameFromId($fk_user)
{
    global $db;

    $u = new User($db);
    if ($u->fetch($fk_user) > 0)
    {
        return $u->getNomUrl(1);
    }

    return '';
}



function _getParcoursNameFromId($id)
{

    $PDOdb = new TPDOdb;

    $obj = new TParcours;

    $obj->load($PDOdb, $id);

    return $obj->name;

}

function getNewRowIdFrom($tablename){

    global $db;

    $sql = "SELECT MAX(rowid) as lastId FROM " .MAIN_DB_PREFIX. $tablename;

    $resql = $db->query($sql);

    $obj = $db->fetch_object($resql);
    if ($obj) {

        return (int) $obj->lastId +1;

    }
    else return 1;


}
