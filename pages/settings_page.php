<?php
declare(strict_types=1);

require_once __DIR__ . '/../template/common.tpl.php';
require_once __DIR__ . '/../framework/Autoload.php';
?>

<?php function drawSettingsPageContent(Request $request)
{
    $db = new PDO("sqlite:" . DB_PATH);
    $user = User::getUserByID($db, getSessionUser($request)['id']);
    ?>

    <main id="settings-page">
        <section id="account-settings">
            <h2>Account Information</h2>
            <div class="information-field">
                <h3>Change Username</h3>
                <input type="text" id="new-username" name="newusername" value="<?= $user->getName() ?>" placeholder="New Username">
            </div>
            <div class="information-field">
                <h3>Change E-mail</h3>
                <input type="e-mail" id="new-email" name="newemail" value="<?= $user->getEmail() ?>" placeholder="New E-mail">
            </div>
            <div class="information-field">
                <h3>Change Password</h3>
                <input type="password" id="new-password" name="newpassword" placeholder="New Password">
            </div>
            <div class="information-field">
                <h3>Change Profile Picture</h3>
                <input type="file" id="image-input" name="image">

                <input type="submit" id="clear-profile-picture" value="Clear">
            </div>
            <br>
            <div class="information-field">
                <h3>Validate with Current Password</h3>
                <input type="password" id="old-password" required name="currentpassword" placeholder="Current Password">
            </div>
            <input type="button" id="settings-button" value="Save">
        </section>
    </main>
<?php } ?>

<?php
function drawSettingsPage(Request $request)
{
    createPage(function () use (&$request) {
        drawMainHeader($request);
        drawSettingsPageContent($request);
        drawFooter();
    }, $request);
} ?>