<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title ?? 'Dashboard'; ?></h1>
            </div>
            <?php if (isset($page_content)): ?>
                <?php echo $page_content; ?>
            <?php endif; ?>
        </main>
    </div>
</div> 