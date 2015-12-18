<?php
if( !function_exists('is_selected') ):
    function is_selected($selected, $option) {
        if( is_array( $selected ) && in_array( $option->ID, $selected ) ) {
            return true;
        }elseif ( !is_array( $selected) && $selected == $option->ID ) {
            return true;
        }

        return false;
    }
endif;
?>

<select name="<?php echo $name; ?>" <?php echo is_array($selected) ? 'multiple' : '' ?>>
    <?php foreach($collection as $option):?>
        <?php $is_selected = is_selected($selected, $option) ? 'selected' : '' ?>
        <option value="<?php echo $option->ID; ?>" <?php echo $is_selected;?>><?php echo $option->post_title ;?></option>
    <?php endforeach;?>
</select>
