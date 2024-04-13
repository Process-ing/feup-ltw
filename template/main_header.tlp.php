<?php declare(strict_types=1); ?>

<?php function drawHeaderButton(string $icon) { ?>
    <button class="header-button">
        <span class="material-icons md-36"><?= $icon ?></span>
    </button>
<?php } ?>

<?php
function drawHamburgerButton() {
    drawHeaderButton('menu');
}
?>

<?php function drawActionButtons() { ?>
    <div id="action-buttons">
        <?php drawHeaderButton('person'); ?>
        <?php drawHeaderButton('shopping_cart'); ?>
    </div>
<?php } ?>

<?php function drawHeaderLogo() { ?>
    <img src="svg/logo_large.svg" alt="Trinted Logo" id="header-logo">
<?php } ?>