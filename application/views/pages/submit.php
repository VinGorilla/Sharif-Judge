<?php
/**
 * Sharif Judge online judge
 * @file submit.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	shj.p={};
	<?php foreach ($problems as $problem){
		$languages = explode(',',$problem['allowed_languages']);
		$items='';
		foreach ($languages as $language){
			$items = $items."'".trim($language)."',";
		}
		$items = substr($items,0,strlen($items)-1);
		echo "shj.p[{$problem['id']}]=[{$items}]; ";
	} ?>
	$(document).ready(function(){
		$("select#problems").change(function(){
			var v = $(this).val();
			$('select#languages').empty();
			$('<option value="0" selected="selected">-- Select One --</option>').appendTo('select#languages');
			if (v==0)
				return;
			for (var i=0;i<shj.p[v].length;i++)
				$('<option value="'+shj.p[v][i]+'">'+shj.p[v][i]+'</option>').appendTo('select#languages');
		});
	});
</script>

<?php $this->view('templates/top_bar'); ?>
<?php $this->view('templates/side_bar',array('selected'=>'submit')); ?>
<?php $now = shj_now(); ?>

<div id="main_container">

	<div id="page_title">
		<img src="<?php echo base_url('assets/images/icons/submit.png') ?>"/>
		<span><?php echo $title ?></span>
	</div>

	<div id="main_content">
		<?php if ($assignment['id']==0): ?>
			<p>Please select an assignment first.</p>
		<?php elseif ($this->user_model->get_user_level($username)==0 && !$assignment['open']): ?>
		<?php // if assignment is closed, non-student users (admin, instructors) still can submit ?>
			<p>Selected assignment is closed.</p>
		<?php elseif ($now < strtotime($assignment['start_time'])): ?>
			<p>Selected assignment has not started.</p>
		<?php elseif ($now > strtotime($assignment['finish_time'])+$assignment['extra_time']): // deadline = finish_time + extra_time?>
			<p>Selected assignment has finished.</p>
		<?php elseif ( !$this->assignment_model->is_participant($assignment['participants'],$username) ): ?>
			<p>You are not registered for submitting.</p>
		<?php else: ?>
			<p>Selected assignment: <?php echo $assignment['name'] ?></p>
			<p>Coefficient: <?php echo $coefficient?>%</p>
			<?php echo form_open_multipart('submit') ?>
			<p class="input_p">
				<label for="problem" class="tiny">Problem:</label>
				<select id="problems" name ="problem" class="sharif_input">
					<option value="0" selected="selected">-- Select One --</option>
					<?php foreach ($problems as $problem): ?>
						<option value="<?php echo $problem['id'] ?>"><?php echo $problem['name'] ?></option>
					<?php endforeach ?>
				</select>
				<?php echo form_error('problem','<div class="shj_error">','</div>'); ?>
			</p>
			<p class="input_p">
				<label for="problem" class="tiny">Language:</label>
				<select id="languages" name="language" class="sharif_input">
					<option value="0" selected="selected">-- Select One --</option>
				</select>
				<?php echo form_error('language','<div class="shj_error">','</div>'); ?>
			</p>
			<p class="input_p">
				<label for="userfile" class="tiny">File:</label>
				<input type="file" id="file" class="sharif_input medium" name="userfile" />
				<?php if ($upload_state==='error'): ?>
				<div class="shj_error">Error uploading file.</div>
				<?php elseif ($upload_state==='ok'): ?>
				<div class="shj_ok">File uploaded successfully. See the result in 'All Submissions'.</div>
				<?php endif ?>
				<?php echo $this->upload->display_errors('<div class="shj_error">','</div>'); ?>
			</p>
			<p class="input_p">
				<input type="submit" value="Submit" class="sharif_input"/>
			</p>
			</form>
		<?php endif ?>

	</div> <!-- main_content -->

</div> <!-- main_container -->