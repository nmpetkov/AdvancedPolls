<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

/**
* the main administration function
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_main() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', '::', ACCESS_EDIT)) {
		return LogUtil::registerPermissionError();
	}

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);

	// Return the output that has been generated by this function
	return $renderer->fetch('advancedpolls_admin_main.htm');
}

/**
* add new item
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_new() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', '::', ACCESS_ADD)) {
		return LogUtil::registerPermissionError();
	}

    // Get the module configuration vars
    $modvars = pnModGetVar('advanced_polls');

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s%]';, array('s' => 'CategoryRegistryUtil')));
        }
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories ('advanced_polls', 'advanced_polls_desc');
        
        $renderer->assign('catregistry', $catregistry);
    }

    $renderer->assign($modvars);

	// Return the output that has been generated by this function
	return $renderer->fetch('advancedpolls_admin_new.htm');
}

/** 
* create a poll
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_create() 
{
    $poll = FormUtil::getPassedValue('poll', isset($args['poll']) ? $args['poll'] : null, 'POST');
    $poll['startDay'] = FormUtil::getPassedValue('startDay', isset($args['startDay']) ? $args['startDay'] : null, 'POST');
    $poll['startMonth'] = FormUtil::getPassedValue('startMonth', isset($args['startMonth']) ? $args['startMonth'] : null, 'POST');
    $poll['startYear'] = FormUtil::getPassedValue('startYear', isset($args['startYear']) ? $args['startYear'] : null, 'POST');
    $poll['startHour'] = FormUtil::getPassedValue('startHour', isset($args['startHour']) ? $args['startHour'] : null, 'POST');
    $poll['startMinute'] = FormUtil::getPassedValue('startMinute', isset($args['startMinute']) ? $args['startMinute'] : null, 'POST');
    $poll['closeDay'] = FormUtil::getPassedValue('closeDay', isset($args['closeDay']) ? $args['closeDay'] : null, 'POST');
    $poll['closeMonth'] = FormUtil::getPassedValue('closeMonth', isset($args['closeMonth']) ? $args['closeMonth'] : null, 'POST');
    $poll['closeYear'] = FormUtil::getPassedValue('closeYear', isset($args['closeYear']) ? $args['closeYear'] : null, 'POST');
    $poll['closeHour'] = FormUtil::getPassedValue('closeHour', isset($args['closeHour']) ? $args['closeHour'] : null, 'POST');
    $poll['closeMinute'] = FormUtil::getPassedValue('closeMinute', isset($args['closeMinute']) ? $args['closeMinute'] : null, 'POST');

	// Confirm authorisation code.
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

	// Notable by its absence there is no security check here. 

	// The API function is called.
	$pollid = pnModAPIFunc('advanced_polls', 'admin', 'create', $poll);

	// The return value of the function is checked
	if ($pollid  != false) {
		// Success
		LogUtil::registerStatus( __('Poll created', $dom));
	}

	// redirect the user to an appropriate page
	return pnRedirect(pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $pollid)));
}

/**
* Modify a Poll
*
* @param 'pollid' the id of the item to be modified
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_modify() 
{
	// Get parameters from whatever input we need.
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
	 
	// Get the poll from the API function
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));
	if ($item == false) {
		return LogUtil::registerError(__('No such item found.', $dom));
	}

	// Security check.
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerPermissionError();
	}

    // Get the module configuration vars
    $modvars = pnModGetVar('advanced_polls');

	// get vote counts
	$votecount = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s%]';, array('s' => 'CategoryRegistryUtil')));
        }
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('advanced_polls', 'advanced_polls_desc');
        
        $renderer->assign('catregistry', $catregistry);
    }

    // assign the item to the template
    $renderer->assign($item);
    $renderer->assign($modvars);
	
	
	// Return the output that has been generated by this function
	return $renderer->fetch('advancedpolls_admin_modify.htm');
}

/**
* Update a poll
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_update() 
{
    $poll = FormUtil::getPassedValue('poll', isset($args['poll']) ? $args['poll'] : null, 'POST');
    $poll['startDay'] = FormUtil::getPassedValue('startDay', isset($args['startDay']) ? $args['startDay'] : null, 'POST');
    $poll['startMonth'] = FormUtil::getPassedValue('startMonth', isset($args['startMonth']) ? $args['startMonth'] : null, 'POST');
    $poll['startYear'] = FormUtil::getPassedValue('startYear', isset($args['startYear']) ? $args['startYear'] : null, 'POST');
    $poll['startHour'] = FormUtil::getPassedValue('startHour', isset($args['startHour']) ? $args['startHour'] : null, 'POST');
    $poll['startMinute'] = FormUtil::getPassedValue('startMinute', isset($args['startMinute']) ? $args['startMinute'] : null, 'POST');
    $poll['closeDay'] = FormUtil::getPassedValue('closeDay', isset($args['closeDay']) ? $args['closeDay'] : null, 'POST');
    $poll['closeMonth'] = FormUtil::getPassedValue('closeMonth', isset($args['closeMonth']) ? $args['closeMonth'] : null, 'POST');
    $poll['closeYear'] = FormUtil::getPassedValue('closeYear', isset($args['closeYear']) ? $args['closeYear'] : null, 'POST');
    $poll['closeHour'] = FormUtil::getPassedValue('closeHour', isset($args['closeHour']) ? $args['closeHour'] : null, 'POST');
    $poll['closeMinute'] = FormUtil::getPassedValue('closeMinute', isset($args['closeMinute']) ? $args['closeMinute'] : null, 'POST');

	// Confirm authorisation code.
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

	// Notable by its absence there is no security check here

	// The API function is called.
	if (pnModAPIFunc('advanced_polls','admin','update', $poll)) {
		// Success
		LogUtil::registerStatus( __('Poll updated', $dom));
	}

	// redirect the user to an appropriate page
	return pnRedirect(pnModURL('advanced_polls', 'admin', 'view'));
}

/**
* delete a poll
*
* @param 'pollid' the id of the item to be deleted
* @param 'confirmation' confirmation that this item can be deleted
*/
function advanced_polls_admin_delete() 
{
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Get the poll
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    if ($item == false) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

	// Security check.
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_DELETE)) {
		return LogUtil::registerPermissionError();
	}

	// Check for confirmation.
	if (empty($confirmation)) {
		// No confirmation yet - display a suitable form to obtain confirmation
		// of this action from the user

		// Create output object
		$renderer = pnRender::getInstance('advanced_polls', false);

		// Assign hidden form value for pollid
		$renderer->assign('pollid', $pollid);             

		// Return the output that has been generated by this function
		return $renderer->fetch('advancedpolls_admin_delete.htm');
	}

	// If we get here it means that the user has confirmed the action

	// Confirm authorisation code.
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

	// The API function is called.
	if (pnModAPIFunc('advanced_polls', 'admin', 'delete', array('pollid' => $pollid))) {
		// Success
		LogUtil::registerStatus( __('Poll deleted', $dom));
	}

	return pnRedirect(pnModURL('advanced_polls', 'admin', 'view'));
}

/**
* Main admin function to view a full list of polls
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_view() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', '::', ACCESS_EDIT)) {
		return LogUtil::registerPermissionError();
	}

    // Get parameters from whatever input we need.
    $startnum = (int)FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
    $property = FormUtil::getPassedValue('advanced_polls_property', isset($args['advanced_polls_property']) ? $args['advanced_polls_property'] : null, 'POST');
    $category = FormUtil::getPassedValue("advanced_polls_{$property}_category", isset($args["advanced_polls_{$property}_category"]) ? $args["advanced_polls_{$property}_category"] : null, 'POST');
    $clear    = FormUtil::getPassedValue('clear', false, 'POST');
    if ($clear) {
        $property = null;
        $category = null;
    }

    // get module vars for later use
    $modvars = pnModGetVar('advanced_polls');

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s%]';, array('s' => 'CategoryRegistryUtil')));
        }
        $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories('advanced_polls', 'advanced_polls_desc');
        $properties = array_keys($catregistry);

        // Validate and build the category filter - mateo
        if (!empty($property) && in_array($property, $properties) && !empty($category)) {
            $catFilter = array($property => $category);
        }

        // Assign a default property - mateo
        if (empty($property) || !in_array($property, $properties)) {
            $property = $properties[0];
        }

        // plan ahead for ML features
        $propArray = array();
        foreach ($properties as $prop) {
            $propArray[$prop] = $prop;
        }
    }

	// get all matching polls
	$items = pnModAPIFunc('advanced_polls', 'user', 'getall',
						  array('checkml' => false,
								'startnum' => $startnum,
								'numitems' => pnModGetVar('advanced_polls', 'adminitemsperpage'),
								'category' => isset($catFilter) ? $catFilter : null,
								'catregistry'  => isset($catregistry) ? $catregistry : null));

    if (!$items)
        $items = array();

	$polls = array();
	foreach ($items as $key => $item) {
        // check if poll is open
        $items[$key]['isopen'] = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $item['pollid']));
        $options = array();
		if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_EDIT)) {
			$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
                               'image' => 'xedit.gif',
							   'title' => __('Edit', $dom));
			if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_DELETE)) {
				$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'delete', array('pollid' => $item['pollid'])),
                                   'image' => '14_layer_deletelayer.gif',
								   'title' => __('Delete', $dom));
			}
			$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'resetvotes', array('pollid' => $item['pollid'])),
                               'image' => 'undo.gif',
							   'title' => __('Reset Votes', $dom));
			$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'duplicate', array('pollid' => $item['pollid'])),
                               'image' => 'editcopy.gif',
							   'title' => __('Duplicate Poll', $dom));
			$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'adminstats', array('pollid' => $item['pollid'])),
                               'image' => 'smallcal.gif',
							   'title' => __('Voting Statistics', $dom));
		}
		$items[$key]['options'] = $options;
	}

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);

    // Assign the items to the template
	$renderer->assign('polls', $items);
    $renderer->assign($modvars);

    // Assign the default language
    $renderer->assign('lang', pnUserGetLang());

    // Assign the categories information if enabled
    if ($modvars['enablecategorization']) {
        $renderer->assign('catregistry', $catregistry);
        $renderer->assign('numproperties', count($propArray));
        $renderer->assign('properties', $propArray);
        $renderer->assign('property', $property);
        $renderer->assign("category", $category);
    }

	// Assign the values for the smarty plugin to produce a pager in case of there
	// being many items to display.
	$renderer->assign('pager', array('numitems' => pnModAPIFunc('advanced_polls', 'user', 'countitems'),
							         'itemsperpage' => $modvars['itemsperpage']));

	// Return the output that has been generated by this function
	return $renderer->fetch('advancedpolls_admin_view.htm');
}

/**
* Modify module configuration
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_modifyconfig() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);
	$renderer->assign(pnModGetVar('advanced_polls'));
	$renderer->assign('dateformats', array('_DATELONG' => ml_ftime(__('%A, %B %d, %Y', $dom),time()),
                                           '_DATETIMEBRIEF' => ml_ftime(__('%b %d, %Y - %I:%M %p', $dom), time()),
                                           '_DATETIMELONG' => ml_ftime(__('%A, %B %d, %Y - %I:%M %p', $dom), time())));
	
	// Return the output that has been generated by this function
	return $renderer->fetch('advancedpolls_admin_modifyconfig.htm');
}

/**
* update module configuration
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_admin_updateconfig() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Confirm authorisation code.
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

    $config = FormUtil::getPassedValue('config', isset($args['config']) ? $args['config'] : null, 'POST');

	// Update module variables.
	if (!isset($config['admindateformat'])) {
		$config['admindateformat'] = 'r';
	}
	pnModSetVar('advanced_polls', 'admindateformat', $config['admindateformat']);
	if (!isset($config['userdateformat'])) {
		$config['userdateformat'] = 'r';
	}
	pnModSetVar('advanced_polls', 'userdateformat', $config['userdateformat']);
	if (!isset($config['usereversedns'])) {
		$config['usereversedns'] = 0;
	}
	pnModSetVar('advanced_polls', 'usereversedns', $config['usereversedns']);
	if (!isset($config['scalingfactor'])) {
		$config['scalingfactor'] = 4;
	}
	pnModSetVar('advanced_polls', 'scalingfactor', $config['scalingfactor']);
	if (!isset($config['adminitemsperpage'])) {
		$config['adminitemsperpage'] = 25;
	}
	pnModSetVar('advanced_polls', 'adminitemsperpage', $config['adminitemsperpage']);
	if (!isset($config['useritemsperpage'])) {
		$config['useritemsperpage'] = 25;
	}
	pnModSetVar('advanced_polls', 'useritemsperpage', $config['useritemsperpage']);
	if (!isset($config['defaultcolour'])) {
	    $config['defaultcolour'] = '#000000';
	}
	pnModSetVar('advanced_polls', 'defaultcolour', $config['defaultcolour']);
	if (!isset($config['defaultoptioncount'])) {
	    $config['defaultoptioncount'] = '12';
	}
	pnModSetVar('advanced_polls', 'defaultoptioncount', $config['defaultoptioncount']);
	if (!isset($config['enablecategorization'])) {
	    $config['enablecategorization'] = false;
	}
	pnModSetVar('advanced_polls', 'enablecategorization', $config['enablecategorization']);
	if (!isset($config['addcategorytitletopermalink'])) {
	    $config['addcategorytitletopermalink'] = false;
	}
	pnModSetVar('advanced_polls', 'addcategorytitletopermalink', $config['addcategorytitletopermalink']);

    // Let any other modules know that the modules configuration has been updated
    pnModCallHooks('module', 'updateconfig', 'advanced_polls', array('module' => 'advanced_polls'));

    // the module configuration has been updated successfuly
    LogUtil::registerStatus (__('Done! Module configuration updated.', $dom));

	// redirect the user to an appropriate page
	return pnRedirect(pnModURL('advanced_polls', 'admin', 'view'));
}

/**
* Reset the votes on a poll
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_admin_resetvotes() 
{
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', '::', ACCESS_EDIT)) {
		return LogUtil::registerPermissionError();
	}

	// Check for confirmation
	if (empty($confirmation)) {
		// No confirmation yet - get one

		// Create output object - this object will store all of our output so that
		// we can return it easily when required
		$renderer = pnRender::getInstance('advanced_polls', false);

		$renderer->assign('pollid', $pollid);
		// Return the output that has been generated by this function
		return $renderer->fetch('advancedpolls_admin_resetvotes.htm');
	}

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

	// Pass to API
	if (pnModAPIFunc('advanced_polls', 'admin', 'resetvotes', array('pollid' => $pollid))) {
		// Success
		LogUtil::registerStatus( _ADVANCEDPOLLSVOTESRESET);
	}

	return pnRedirect(pnModURL('advanced_polls', 'admin', 'view'));
}

/**
* Display voting statistics to admin
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_admin_adminstats() 
{
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Get parameters
	$pollid = pnVarCleanFromInput('pollid');
	$sortorder = pnVarCleanFromInput('sortorder');
	$sortby = pnVarCleanFromInput('sortby');
	$startnum = pnVarCleanFromInput('startnum');
	
	// set default sort order
	if (!isset($sortorder)) {
		$sortorder = 0;
	}
	// set default sort by
	if (!isset($sortby)) {
		$sortby = 1;
	}

    // Create output object
	$renderer = pnRender::getInstance('advanced_polls', false);
	
	// get all votes for this poll from api
	$votes = pnModAPIFunc('advanced_polls', 'admin', 'getvotes', array('pollid' => $pollid,
																	   'sortorder' => $sortorder,
																	   'sortby' => $sortby,
																	   'startnum' => $startnum,
                                                                       'numitems' => pnModGetVar('advanced_polls',
                                                                                                 'adminitemsperpage')));

	// get all votes for this poll from api
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	$renderer->assign('item', $item);
	$renderer->assign('pollid', $pollid);
	$votecountarray = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid'=>$pollid));
	$votecount = $votecountarray['totalvotecount'];
	$renderer->assign('votecount', $votecount);
	$renderer->assign('sortby', $sortby);
	$renderer->assign('sortorder', $sortorder);

	if ($votes == true ) {
		foreach ($votes as $key => $vote) {
			if (pnModGetVar('advanced_polls', 'usereversedns')) {
				$host = gethostbyaddr($vote['ip']) . ' - ' . $vote['ip'];
			} else {
				$host = $vote['ip'];
			}
			$voteoffset = $vote['optionid']-1;
            $votes[$key]['user'] = pnUserGetVar('uname',$vote['uid']);
            $votes[$key]['optiontext'] = $item['options'][$voteoffset]['optiontext'];
		}
	}		
	$renderer->assign('votes', $votes);

	// Assign the values for the smarty plugin to produce a pager in case of there
	// being many items to display.
	$renderer->assign('pager', array('numitems' => $votecount,
							         'itemsperpage' => pnModGetVar('advanced_polls', 'adminitemsperpage')));

	return $renderer->fetch('advancedpolls_admin_adminstats.htm');
}

/**
* Duplicate poll
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_admin_duplicate() 
{
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	if ($item == false) {
		return LogUtil::registerError(__('No such item found.', $dom));
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerPermissionError();
	}

	// Check for confirmation.
	if (empty($confirmation)) {
		// No confirmation yet - display a suitable form to obtain confirmation
		// of this action from the user

		// Create output object
		$renderer = pnRender::getInstance('advanced_polls', false);

		// Assign a hidden form value for the poll id
		$renderer->assign('pollid', $pollid);

		// Return the output that has been generated by this function
		return $renderer->fetch('advancedpolls_admin_duplicate.htm');
	}

	// If we get here it means that the user has confirmed the action

	// Confirm authorisation code.
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'admin', 'view'));
	}

	// The API function is called
	if (pnModAPIFunc('advanced_polls', 'admin', 'duplicate', array('pollid' => $pollid))) {
		// Success
		LogUtil::registerStatus( __('Poll Duplicated', $dom));
	}

	// redirect the user to an appropriate page
	return pnRedirect(pnModURL('advanced_polls', 'admin', 'view'));
}
