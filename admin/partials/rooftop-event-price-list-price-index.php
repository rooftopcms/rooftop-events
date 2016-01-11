<?php if( count( $prices ) ): ?>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th></th>
                <th>Price</th>
                <th>Band</th>
                <th>Type</th>
            </tr>
        </thead>
        
        <tbody>
        <?php foreach( $prices as $row ): ?>
            <?php
            $price_meta = get_post_meta( $row->ID, 'event_price_meta', true );
            $associated_ids = [];

            $associated_ids[] = get_post_meta($row->ID, 'price_band_id', true);
            $associated_ids[] = get_post_meta($row->ID, 'ticket_type_id', true);
            $associated_posts = get_posts(array('posts_per_page' => -1, 'post__in' => $associated_ids, 'post_type' => array('event_price_band', 'event_ticket_type'), 'orderby' => 'post_type', 'order' => 'ASC'));

            list($price_band, $ticket_type) = $associated_posts;
            ?>
            <tr data-event-price-id="<?php echo $row->ID;?>">
                <td>
                    <a href="?post=<?php echo $row->ID ?>&action=edit">Edit</a>
                </td>
                <td>£<?php echo $price_meta['ticket_price'] ;?></td>
                <td><?php echo $price_band ? $price_band->post_title : '' ;?></td>
                <td><?php echo $ticket_type ? $ticket_type->post_title : '' ;?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
<?php else: ?>
    <?php echo "No prices" ?>
<?php endif; ?>

<hr/>
<a href="/wp-admin/post-new.php?post_type=event_price&price_list_id=<?php echo get_the_ID();?>">Add New Price</a>
