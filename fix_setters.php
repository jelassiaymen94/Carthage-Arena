<?php
$files = [
    'src/Entity/UserSkin.php' => 'public function setPurchasedAt',
    'src/Entity/User.php' => 'public function setCreatedAt',
    'src/Entity/TeamMembership.php' => 'public function setJoinedAt',
    'src/Entity/Team.php' => 'public function setCreatedAt',
    'src/Entity/Skin.php' => 'public function setCreatedAt',
    'src/Entity/ReclamationResponse.php' => ['public function setCreatedAt', 'public function setAuthor'],
    'src/Entity/Reclamation.php' => ['public function setCreatedAt', 'public function setUpdatedAt', 'public function setAuthor'],
    'src/Entity/Profile.php' => ['public function setCreatedAt', 'public function setUpdatedAt'],
    'src/Entity/PasswordResetToken.php' => 'public function setExpiresAt',
    'src/Entity/Merch.php' => 'public function setCreatedAt',
    'src/Entity/MatchEntity.php' => ['public function setScheduledAt', 'public function setStartedAt', 'public function setCompletedAt'],
    'src/Entity/License.php' => ['public function setCreatedAt', 'public function setUsedAt'],
    'src/Entity/Game.php' => 'public function setCreatedAt',
    'src/Entity/AuthToken.php' => 'public function setExpiresAt'
];

foreach ($files as $file => $searches) {
    if (!is_array($searches)) $searches = [$searches];
    $content = file_get_contents($file);
    foreach ($searches as $search) {
        $content = str_replace($search, str_replace('public ', 'protected ', $search), $content);
    }
    file_put_contents($file, $content);
}
echo "Done";
