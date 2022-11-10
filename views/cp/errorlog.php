<div class="box panel">
    <div class="panel-heading">
        <div class="title-bar">
            <h3 class="title-bar__title title-bar--large"><?=$heading?></h3>
            <div class="filters-toolbar title-bar__extra-tools">
            </div>
        </div>
    </div>
    <div class="filter-search-bar">
        <div class="filter-search-bar__filter-row">
            <?=$filters?>
        </div>
    </div>

    <div class="table-responsive table-responsive--collapsible">
    <?=$table?>
    </div>

    <?=$pagination?>
</div>