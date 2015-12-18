<?php if( count( $event_instances ) ): ?>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th></th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Availability</th>
            </tr>
        </thead>
        
        <tbody>
        <?php foreach( $event_instances as $row ): ?>

            <?php
            $instance_meta = get_post_meta($row->ID, 'event_instance_availability', true);
            $instance_meta = is_array($instance_meta) ? $instance_meta : [];
            ?>

            <tr data-event-instance-id="<?php echo $row->ID;?>">
                <td>
                    <a href="?post=<?php echo $row->ID ?>&action=edit">Edit</a>
                </td>
                <td><?php echo array_key_exists('starts_at', $instance_meta) ? $instance_meta['starts_at'] : '' ;?></td>
                <td><?php echo array_key_exists('stops_at', $instance_meta)  ? $instance_meta['stops_at']  : '' ;?></td>
                <td>
                    <?php if( array_key_exists('seats_available', $instance_meta) ): ?>
                        <?php echo $instance_meta['seats_available'] ;?>
                        remaining <span class="hint">(from a total capacity of <?php echo $instance_meta['seats_capacity'] ;?>)</span>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>

<?php else: ?>
    <?php echo "No event instances" ?>
<?php endif; ?>

<hr/>
<a href="/wp-admin/post-new.php?post_type=event_instance&event_id=<?php echo get_the_ID();?>">Add New Event Instance</a>