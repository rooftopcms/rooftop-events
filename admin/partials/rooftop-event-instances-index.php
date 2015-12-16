<?php if( count( $event_instances ) ): ?>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Availability</th>
            </tr>
        </thead>
        
        <tbody>
        <?php foreach( $event_instances as $row ): ?>
            <?php $instance_meta = get_post_meta($row->ID); ?>

            <?php print_r($instance_meta); ?>
            <tr data-event-instance-id="<?php echo $row->ID;?>">
                <td><?php echo array_key_exists('starts_at', $instance_meta) ? $instance_meta['starts_at'] : '' ;?></td>
                <td><?php echo array_key_exists('stops_at', $instance_meta)  ? $instance_meta['stops_at']  : '' ;?></td>
                <td>
                    <?php if( array_key_exists('seats_available', $instance_meta) ): ?>
                        <span>(<?php echo $instance_meta['seats_capacity'] ;?>)</span>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>

<?php else: ?>
    <?php echo "No events" ?>
<?php endif; ?>