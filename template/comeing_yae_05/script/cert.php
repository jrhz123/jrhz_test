<?php

$Cis['style']['cert'] = array(
'{ADDONVAR:SN}',
'{ADDONVAR:RevisionID}',
'{ADDONVAR:RevisionDateline}',
);
$Cis['style']['sn'] = '{ADDONVAR:MD5(SN,RevisionID,RevisionDateline)}';
?>
