<h1 class="heading">Send Email</h1>

<?php if($result != '') : ?>
<pre><?php print_r($result); ?></pre>
<?php endif; ?>


<?php echo form_open('administrator/dashboard/mail'); ?>

    <input type="text" name="email_to" class="input-xxlarge" placeholder="Email Address" value="<?php echo $input_email_to; ?>" />
    <textarea name="email_body" rows="15" cols="40" placeholder="Email Body" style="width: 100%"><?php if (isset($query)) { echo $query; } ?></textarea><br /><br />

    <input type="submit" value="Send Email" class="btn btn-primary" />

<?php echo form_close(); ?>