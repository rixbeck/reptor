<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

$query = <<<SQL
"select activity.partner,
       activity.office,
       activity.manager,
       activity.username,
       activity.calls_win,
       activity.calls,
       activity.commissions,
       activity.commissions_research,
       activity.exclusive_commissions,
       activity.exclusive_rate,
       activity.standard_commissions,
       activity.special_commissions,
       activity.contracts,
       activity.share,
       activity.ratings,
       activity.user_active_status
from activity
WHERE 1=1 " ~ 
(params['partner'] ? " AND activity.partner in (" ~ implode(',', params['partner']) ~ ") " : "")  ~
"
ORDER BY activity.partner ASC"

SQL;

return $query;
