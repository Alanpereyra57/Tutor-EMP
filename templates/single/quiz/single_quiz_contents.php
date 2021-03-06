<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$course = tutor_utils()->get_course_by_quiz(get_the_ID());
?>

<div class="tutor-single-page-top-bar">
    <div class="tutor-topbar-item tutor-hide-sidebar-bar">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">Temario<i class="tutor-icon-angle-left"></i> </a>

    </div>

		<div class="next-lesson-ip ml-auto">
			<?php next_post_link( '%link', 'Siguiente lección'); ?>
		</div>

    <div class="tutor-topbar-item tutor-topbar-content-title-wrap">

			<!-- <?php $course_id = get_post_meta(get_the_ID(), '_tutor_course_id_for_lesson', true); ?>
			<a href="<?php echo get_the_permalink($course_id); ?>" class="tutor-topbar-home-btn">
					<i class="tutor-icon-home"></i> <?php echo __('Página principal', 'tutor') ; ?>
			</a> -->
        <!-- <?php
        tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);
        the_title(); ?> -->
    </div>

</div>


<div class="tutor-quiz-single-wrap ">
    <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

	<?php
	if ($course){
		tutor_single_quiz_top();
		tutor_single_quiz_body();
	}else{
		tutor_single_quiz_no_course_belongs();
	}
	?>
</div>
