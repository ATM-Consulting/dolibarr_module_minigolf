<?php

require 'config.php';
dol_include_once('/minigolf/class/minigolf.class.php');
dol_include_once('/minigolf/lib/minigolf.lib.php');

if(empty($user->rights->minigolf->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('minigolf@minigolf');

$PDOdb = new TPDOdb;

$object = new TParcours();

$action = GETPOST('action');

$hookmanager->initHooks(array('minigolfHook'));

/*
 * Actions
 */


$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ...

    switch ($action){

        case 'delete' :

            $rowid = GETPOST('rowid');

            $object->load($PDOdb, $rowid);

            $object->to_delete = true;

            $object->save($PDOdb);

            // suppression de l'association des tous a ce parcours

            $cleanup = new TParcoursTrou();

            $cleanup->removeAssocFor($rowid);

            header('Location: '.dol_buildpath('/minigolf/listParcours.php', 1) );
            exit;

            break;
    }

}


/*
 * View
 */

llxHeader('',$langs->trans('ListeDesParcours'),'','');

//$type = GETPOST('type');
//if (empty($user->rights->mymodule->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT t.rowid, t.name, t.difficulty , t.rowid as dellink' ; //, t.date_cre, t.date_maj, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.'minigolf_parcours t ';

//$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('MyModule', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_minigolfParcours', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new TListviewTBS('minigolf');
echo $r->render($PDOdb, $sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()

    ,'link' => array('name' => '<a href="cardParcours.php?id=@rowid@&action=edit">@val@</a>'
    , 'dellink' => '<a href="listParcours.php?rowid=@dellink@&action=delete">X</a>'
    )

	,'type' => array(
		'date_cre' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'date_maj' => 'date'
	)
	/*,'search' => array(
		'date_cre' => array('recherche' => 'calendars', 'allow_is_null' => true)
		,'date_maj' => array('recherche' => 'calendars', 'allow_is_null' => false)
		,'ref' => array('recherche' => true, 'table' => 't', 'field' => 'ref')
		,'label' => array('recherche' => true, 'table' => array('t', 't'), 'field' => array('label', 'description')) // input text de recherche sur plusieurs champs
		,'status' => array('recherche' => TMymodule::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)*/
	,'translate' => array()
	,'hide' => array(
		'rowid' , 'date_cre' , 'date_maj'
	)
	,'liste' => array(
		'titre' => $langs->trans('ListeDesParcours')
		,'image' => img_picto('','title_generic.png', '', 0)
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NoMyModule')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'name' => $langs->trans('nom.')
        ,'dellink' => $langs->trans('dellink')
		,'difficulty' => $langs->trans('Difficulté')
		,'date_cre' => $langs->trans('DateCre')
		,'date_maj' => $langs->trans('DateMaj')
	)
	,'eval'=>array(
//		'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

echo '<a class="button"  href="' .  dol_buildpath('/minigolf/cardParcours.php',1) .'?action=create">' . $langs->trans("Créer Nouveau Parcours") . '</a>';

llxFooter('');

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;
	
	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}
	
	return '';
}