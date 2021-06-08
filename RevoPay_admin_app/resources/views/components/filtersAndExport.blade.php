<div class="row marginb-20">
    @include('components.itemspage')
    <div class="col-md-9 col-xs-4 text-center rapyd-filters" id="filtersAndExportDiv">
        {!! $filter !!}
        @include('components.advancedFilters')
    </div>
    @if(!isset($notexportbutton))
    <div class="col-md-2 col-xs-5 text-right" id="exportDropdownDiv">
        @include('components.exportDropdown')
    </div>
    @endif
</div>


