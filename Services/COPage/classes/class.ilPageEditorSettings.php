<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Page editor settings
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesCOPage
*/
class ilPageEditorSettings
{
	// settings groups. each group contains one or multiple
	// page parent types
	protected static $option_groups = array(
		"lm" => array("lm", "dbk"),
		"wiki" => array("wpg"),
		"scorm" => array("sahs"),
		"glo" => array("gdf"),
		"test" => array("qpl"),
		"rep" => array("root", "cat", "grp", "crs", "fold")
		);
		
	/**
	* Get all settings groups
	*/
	static function getGroups()
	{
		return self::$option_groups;
	}
	
	/**
	* Write Setting
	*/
	static function writeSetting($a_grp, $a_name, $a_value)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM page_editor_settings WHERE ".
			"settings_grp = ".$ilDB->quote($a_grp, "text").
			" AND name = ".$ilDB->quote($a_name, "text")
			);
		
		$ilDB->manipulate("INSERT INTO page_editor_settings ".
			"(settings_grp, name, value) VALUES (".
			$ilDB->quote($a_grp, "text").",".
			$ilDB->quote($a_name, "text").",".
			$ilDB->quote($a_value, "text").
			")");
	}
	
	/**
	* Lookup setting
	*/
	static function lookupSetting($a_grp, $a_name, $a_default = false)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT value FROM page_editor_settings ".
			" WHERE settings_grp = ".$ilDB->quote($a_grp, "text").
			" AND name = ".$ilDB->quote($a_name, "text")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["value"];
		}
		
		return $a_default;
	}
	
	/**
	* Lookup setting by parent type
	*/
	static function lookupSettingByParentType($a_par_type, $a_name, $a_default = false)
	{
		foreach(self::$option_groups as $g => $types)
		{
			if (in_array($a_par_type, $types))
			{
				$grp = $g;
			}
		}
		
		if ($grp != "")
		{
			return ilPageEditorSettings::lookupSetting($grp, $a_name, $a_default);
		}
		
		return $a_default;
	}

}
