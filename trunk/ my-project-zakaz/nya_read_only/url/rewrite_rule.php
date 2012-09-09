<?php
/**
 * Rewrite rules for URL scheme.
 *
 * @copyright (C) 2011 hcs habr extension for PunBB (C)
 * @copyright Copyright (C) 2011 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package habr
 */

$forum_rewrite_rules['/^ro[\/_-]?(add|del)[\/_-]?([0-9a-z]+)[\/_-]?([0-9a-z]+)(\.html?|\/)?$/i'] = 'misc.php?r=nya_read_only/ro/$1/uid/$2/token/$3';
