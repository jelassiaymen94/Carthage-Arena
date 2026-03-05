<?php
$files = [
    'src/Entity/UserSkin.php' => 'protected function setPurchasedAt',
    'src/Entity/User.php' => 'protected function setCreatedAt',
    'src/Entity/TeamMembership.php' => 'protected function setJoinedAt',
    'src/Entity/Team.php' => 'protected function setCreatedAt',
    'src/Entity/Skin.php' => 'protected function setCreatedAt',
    'src/Entity/ReclamationResponse.php' => ['protected function setCreatedAt', 'protected function setAuthor'],
    'src/Entity/Reclamation.php' => ['protected function setCreatedAt', 'protected function setUpdatedAt', 'protected function setAuthor'],
    'src/Entity/Profile.php' => ['protected function setCreatedAt', 'protected function setUpdatedAt'],
    'src/Entity/PasswordResetToken.php' => 'protected function setExpiresAt',
    'src/Entity/Merch.php' => 'protected function setCreatedAt',
    'src/Entity/MatchEntity.php' => ['protected function setScheduledAt', 'protected function setStartedAt', 'protected function setCompletedAt'],
    'src/Entity/License.php' => ['protected function setCreatedAt', 'protected function setUsedAt'],
    'src/Entity/Game.php' => 'protected function setCreatedAt',
    'src/Entity/AuthToken.php' => 'protected function setExpiresAt'
];

foreach ($files as $file => $searches) {
    if (!is_array($searches)) $searches = [$searches];
    $content = file_get_contents($file);
    foreach ($searches as $search) {
        $content = str_replace($search, str_replace('protected ', 'public ', $search), $content);
    }
    file_put_contents($file, $content);
}
echo "Done";
