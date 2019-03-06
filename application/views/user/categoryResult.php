<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="exam-cont" class="row-fluid">
	<div class="span12">
		<?php 
		if(!isset($otherDatas))
		{
		?>
		<h2 class="title">User Category wise Results</h2>
		<?php } ?>
		<div class="review-main">
		    <div class="well exam-info">
		    	<div class="row-fluid">
		    		 
		    		<?php 
		    		if(isset($Datassss) && count($Datassss)>0)
		    		{

		    			foreach ($Datassss as $key => $value) { ?>
				        <div class="span12">
				            <h3 class="title">Category Name :: <?php echo $key; ?></h3>
				                <p class="meta">
					                <strong>Total Wrong Marks ::</strong>
					                <span><?php echo $value['wrong']; ?></span>
				            	</p>
				            	<p class="meta">
					                <strong>Total Correct Marks ::</strong>
					                <span><?php echo $value['correct']; ?></span>
				            	</p>
				            	<p class="meta">
					                <strong>Total Wrong :: </strong>
					                <span><?php echo $value['wrongCount']; ?></span>
				            	</p>
				            	<p class="meta">
					                <strong>Total Correct ::</strong>
					                <span><?php echo $value['correctCount']; ?></span>
				            	</p>
				        </div>
				        <?php }
		    		}


		    		if(isset($otherDatas))
		    		{
		    			?>
				        <div class="span12">
				            <h3 class="title">Subject wise Result</h3>
				            	<?php 
				            	foreach($otherDatas as $value) 
				            	{ 
				            	?>
				                <p class="meta">
					                <strong><?php echo $value['subject_name']; ?></strong>
					                <span>Total <b><?php echo $value['mark']; ?></b> Marks</span>
				            	</p>
				            	<?php 
				            	} 
				            	?>
				        </div>
				    <?php  
					} 
					?>
		    	</div>
			</div>
		</div>
	</div>
</div>

 