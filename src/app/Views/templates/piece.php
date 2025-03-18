<button 
<?php if($players[$active]->getId() != $_SESSION['player']->getId() || $active != $piece->getCouleur()) {?> disabled="true" <?php } ?>
    type="submit" 
    name="action" 
    value="validate" 
    class="piece <?= $piece_details['class'] ?> 
    <?= $piece_details['direction'] ?>" style="background:<?= $piece_details['color-code'] ?>"> <?= $piece_details['name'] ?>  </button>
