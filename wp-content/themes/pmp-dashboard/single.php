<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package PMP_Dashboard
 */

get_header();
?>

<!-- Header from front-page.php -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-6 py-2 flex items-center justify-between border-b border-border-color">
        <a href="<?php echo home_url(); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" alt="PMP Exam Prep" class="w-20 h-auto">
        </a>
        <div class="hidden md:flex space-x-8">
            <a href="<?php echo home_url(); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Home</a>
            <a href="<?php echo get_post_type_archive_link('work_group'); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Courses</a>
            <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">My Learning</a>
            <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Resources</a>
            <a href="<?php echo get_permalink(get_page_by_path('community')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Community</a>
            <a href="<?php echo get_permalink(get_page_by_path('about')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 transition-colors duration-300">About</a>
        </div>
        <div class="flex items-center space-x-2">
            <?php if (is_user_logged_in()) : ?>
                <div class="relative">
                    <button class="flex items-center text-secondary hover:text-primary px-3 py-2 transition-colors duration-300" onclick="toggleDropdown()">
                        <span class="font-bold">MY ACCOUNT</span>
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div id="accountDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">Dashboard</a>
                        <a href="<?php echo get_edit_user_link(); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <hr class="my-1">
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">Logout</a>
                    </div>
                </div>
            <?php else : ?>
                <a href="<?php echo wp_login_url(); ?>" class="text-secondary hover:text-primary px-3 py-2 transition-colors duration-300">Login</a>
                <a href="<?php echo wp_registration_url(); ?>" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors duration-300">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="bg-white">
	<main id="main" class="max-w-4xl mx-auto px-6 py-12 site-main">

		<?php
		while ( have_posts() ) : the_post();
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('prose lg:prose-xl max-w-none'); ?>>
            <header class="entry-header mb-8">
                <?php the_title( '<h1 class="entry-title text-4xl font-bold text-secondary leading-tight">', '</h1>' ); ?>
                <div class="entry-meta text-gray-600 mt-2">
                    <span class="posted-on">Posted on <?php echo get_the_date(); ?></span>
                    <span class="byline"> by <?php the_author_posts_link(); ?></span>
                </div>
            </header><!-- .entry-header -->

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail mb-8">
                    <?php the_post_thumbnail('large', ['class' => 'w-full h-auto rounded-lg shadow-md']); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'pmp-dashboard' ),
                    'after'  => '</div>',
                ) );
                ?>
            </div><!-- .entry-content -->

            <footer class="entry-footer mt-12 pt-8 border-t border-gray-200">
                <div class="post-tags">
                    <?php the_tags( '<span class="tag-links">' . __( 'Tagged ', 'pmp-dashboard' ) . '', ', ', '</span>' ); ?>
                </div>
            </footer><!-- .entry-footer -->
        </article><!-- #post-<?php the_ID(); ?> -->

        <?php
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

	</main><!-- #main -->
</div><!-- .bg-white -->

<script>
function toggleDropdown() {
    document.getElementById('accountDropdown').classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('accountDropdown');
    const button = event.target.closest('button');
    if (!button || !button.contains(event.target)) {
        if (dropdown && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    }
});
</script>

<?php
get_footer();
