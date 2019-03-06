<h1 class="heading">Run Query</h1>

<?php if($result != '') : ?>
<pre><?php print_r($result); ?></pre>
<?php endif; ?>


<?php echo form_open('administrator/dashboard/query'); ?>

    <input type="password" name="secret" />

    <textarea name="query" id="query" rows="15" cols="40" style="width: 100%"><?php if (isset($query)) { echo $query; } ?></textarea><br /><br />
    <input type="submit" value="Run Query" class="btn btn-primary" />

<?php echo form_close(); ?>