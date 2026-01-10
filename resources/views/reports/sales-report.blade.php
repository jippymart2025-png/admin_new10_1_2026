@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.reports_sale')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/reports/sales')}}">{{trans('lang.report_plural')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.reports_sale')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card  pb-4">
                <div class="card-body">
                    <div class="error_top"></div>
                    <div class="row restaurant_payout_create">
                        <div class="restaurant_payout_create-inner">
                            <fieldset>
                                <legend>{{trans('lang.reports_sale')}}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.select_restaurant')}}</label>
                                    <div class="col-7">
                                        <select class="form-control restaurant">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.select_driver')}}</label>
                                    <div class="col-7">
                                        <select class="form-control driver">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.select_user')}}</label>
                                    <div class="col-7">
                                        <select class="form-control customer">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.select_category')}}</label>
                                    <div class="col-7">
                                        <select class="form-control category">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- <div class="form-group row width-50">
                                     <label class="col-3 control-label">{{trans('lang.select_payment_method')}}</label>
                                     <div class="col-7">
                                         <select class="form-control payment_method">
                                             <option value="">{{trans('lang.all')}}</option>
                                         </select>
                                     </div>
                                 </div>--}}
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.select_date')}}</label>
                                    <div class="col-7">
                                        <div id="reportrange"
                                             style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.file_format')}}<span
                                            class="required-field"></span></label>
                                    <div class="col-7">
                                        <select class="form-control file_format">
                                            <option value="">{{trans('lang.file_format')}}</option>
                                            <option value="csv">{{trans('lang.csv')}}</option>
                                            <option value="pdf">{{trans('lang.pdf')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary do-not-download"><i
                                class="fa fa-save"></i> {{ trans('lang.download')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script>
        // Load options from SQL
        var currentCurrency = '';
        var decimal_degits = 2;
        var symbolAtRight = false;
        function loadOptions(){
            return $.getJSON('{{ route("reports.sales.options") }}').then(function(resp){
                try{
                    var c = resp.currency || {};
                    currentCurrency = c.symbol || '₹';
                    decimal_degits = c.decimal_degits || 2;
                    symbolAtRight = !!c.symbolAtRight;
                }catch(e){}
                (resp.vendors||[]).forEach(function(v){ $('.restaurant').append('<option value="'+v.id+'">'+(v.title||'')+'</option>'); });
                (resp.drivers||[]).forEach(function(d){ $('.driver').append('<option value="'+d.id+'">'+(d.firstName||'')+' '+(d.lastName||'')+'</option>'); });
                (resp.customers||[]).forEach(function(u){ $('.customer').append('<option value="'+u.id+'">'+(u.firstName||'')+' '+(u.lastName||'')+'</option>'); });
                (resp.categories||[]).forEach(function(c){ $('.category').append('<option value="'+c.id+'">'+(c.title||c.id)+'</option>'); });
            });
        }
        setDate();
        function setDate() {
            var start = moment().subtract(29, 'days');
            var end = moment();
            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
            cb(start, end);
        }
        // Initialize options
        loadOptions();
        function loadScriptOnce(url){
            return new Promise(function(resolve,reject){
                if (document.querySelector('script[src="'+url+'"]')) {
                    resolve();
                    return;
                }
                const script=document.createElement('script');
                script.src=url;
                script.onload=()=>resolve();
                script.onerror=reject;
                document.head.appendChild(script);
            });
        }
        async function exportAsPdf(orderData, headers){
            if (!(window.jspdf && window.jspdf.jsPDF)) {
                await loadScriptOnce('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
            }
            if (!(window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API.autoTable)) {
                await loadScriptOnce('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js');
            }
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l','pt');
            doc.setFontSize(16);
            doc.text('Sales Report', 40, 40);
            const headerRow = headers.map(function(h){
                return (typeof h === 'object' && h.alias) ? h.alias : h;
            });
            const bodyRows = orderData.map(function(row){
                return headerRow.map(function(head){
                    return row[head] !== undefined ? row[head] : '';
                });
            });
            doc.autoTable({
                startY: 60,
                head: [headerRow],
                body: bodyRows,
                styles: { fontSize: 9, overflow: 'linebreak' },
                headStyles: { fillColor: [243, 108, 33] },
            });
            doc.save('sales-report.pdf');
        }
        async function generateReport(orderData, headers, fileFormat) {

            const lowerFormat = String(fileFormat || '').toLowerCase();

            // Handle PDF
            if (lowerFormat === 'pdf') {
                await exportAsPdf(orderData, headers);
                return;
            }

            // Allowed formats
            if (!['csv', 'xls', 'xlsx'].includes(lowerFormat)) {
                throw new Error("Unsupported format: " + fileFormat);
            }

            // Load exporter only once
            if (typeof objectExporter !== "function") {
                await loadScriptOnce("https://unpkg.com/object-exporter@3.2.1/dist/objectexporter.min.js");
            }

            // Correct type
            const normalizedType = (lowerFormat === "csv") ? "csv" : "excel";

            objectExporter({
                type: normalizedType,   // <-- FIXED
                exportable: orderData,
                headers: headers,
                fileName: "sales-report",
                columnSeparator: ",",
                sheetName: "sales-report"
            });
        }

        function formatCurrency(v){
            v = parseFloat(v || 0).toFixed(decimal_degits);
            return symbolAtRight ? (v + currentCurrency) : (currentCurrency + v);
        }
        function mapRow(r){
            return {
                'restaurantorders ID': r.order_id,
                'Restaurant Name': r.restaurant,
                'Driver Name': r.driver_name || '',
                'Driver Email': r.driver_email || '',
                'Driver Phone': r.driver_phone || '',

                'User Name': r.user_name || '',
                'User Email': r.user_email || '',
                'User Phone': r.user_phone || '',

                'Date': r.date,
                'Category': r.category,
                'Payment Method': r.payment_method,
                'Total': formatCurrency(r.total||0),
                'Admin Commission': formatCurrency(r.admin_commission||0)
            };
        }
        function getProductsTotal(snapshotsProducts) {
            var adminCommission = snapshotsProducts.adminCommission;
            var discount = snapshotsProducts.discount;
            var couponCode = snapshotsProducts.couponCode;
            var extras = snapshotsProducts.extras;
            var extras_price = snapshotsProducts.extras_price;
            var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
            var takeAway = snapshotsProducts.takeAway;
            var tip_amount = snapshotsProducts.tip_amount;
            var status = snapshotsProducts.status;
            var products = snapshotsProducts.products;
            var deliveryCharge = snapshotsProducts.deliveryCharge;
            var totalProductPrice = 0;
            var total_price = 0;
            var specialDiscount = snapshotsProducts.specialDiscount;
            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
            if (products) {
                products.forEach((product) => {
                    var val = product;
                    if (val.price) {
                        price_item = parseFloat(val.price).toFixed(2);
                        extras_price_item = 0;
                        if (val.extras_price && !isNaN(extras_price_item) && !isNaN(val.quantity)) {
                            extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(2);
                        }
                        if (!isNaN(price_item) && !isNaN(val.quantity)) {
                            totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                        }
                        var extras_price = 0;
                        if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                            extras_price = extras_price_item;
                        }
                        totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                        totalProductPrice = parseFloat(totalProductPrice).toFixed(2);
                        if (!isNaN(totalProductPrice)) {
                            total_price += parseFloat(totalProductPrice);
                        }
                    }
                });
            }
            if (intRegex.test(discount) || floatRegex.test(discount)) {
                discount = parseFloat(discount).toFixed(decimal_degits);
                total_price -= parseFloat(discount);
            }
            var special_discount = 0;
            if (specialDiscount != undefined) {
                special_discount = parseFloat(specialDiscount.special_discount).toFixed(2);
                total_price = total_price - special_discount;
            }
            tax = 0;
            if (snapshotsProducts.hasOwnProperty('taxSetting')) {
                var total_tax_amount = 0;
                for (var i = 0; i < snapshotsProducts.taxSetting.length; i++) {
                    var data = snapshotsProducts.taxSetting[i];
                    if (data.type && data.tax) {
                        if (data.type == "percentage") {
                            tax = (data.tax * total_price) / 100;
                        } else {
                            tax = data.tax;
                        }
                    }
                    total_tax_amount += parseFloat(tax);
                }
                total_price = parseFloat(total_price) + parseFloat(total_tax_amount);
            }
            if ((intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) && !isNaN(deliveryCharge)) {
                deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
                total_price += parseFloat(deliveryCharge);
            }
            if (intRegex.test(tip_amount) || floatRegex.test(tip_amount) && !isNaN(tip_amount)) {
                tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
                total_price += parseFloat(tip_amount);
                total_price = parseFloat(total_price).toFixed(decimal_degits);
            }
            return total_price;
        }
        $(document).on('click', '.do-not-download', function () {
            var restaurant = $(".restaurant :selected").val();
            var driver = $(".driver :selected").val();
            var customer = $(".customer :selected").val();
            var category = $(".category :selected").val();
            var payment_method = $(".payment_method :selected").val();
            var fileFormat = $(".file_format :selected").val();
            let start_date = moment($('#reportrange').data('daterangepicker').startDate).toDate();
            let end_date = moment($('#reportrange').data('daterangepicker').endDate).toDate();
            var headerArray = ['restaurantorders ID', 'Restaurant Name', 'Driver Name', 'Driver Email', 'Driver Phone', 'User Name', 'User Email', 'User Phone', 'Date', 'Category', 'Payment Method', 'Total', 'Admin Commission'];
            var headers = [];
            $(".error_top").html("");
            if (fileFormat == 'xls' || fileFormat == 'csv' || fileFormat == "pdf") {
                headers = headerArray;
                // var script = document.createElement("script");
                // script.setAttribute("src", "https://unpkg.com/object-exporter@3.2.1/dist/objectexporter.min.js");
                var head = document.head;
                // head.insertBefore(script, head.firstChild);
            } else {
                for (var k = 0; k < headerArray.length; k++) {
                    headers.push({
                        alias: headerArray[k],
                        name: headerArray[k],
                        flex: 1,
                    });
                }
                var script = document.createElement("script");
                script.setAttribute("src", "{{ asset('js/objectexporter.min.js') }}");
                script.setAttribute("async", "false");
                var head = document.head;
                head.insertBefore(script, head.firstChild);
            }
            if (fileFormat == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.file_format_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#overlay").show();
                $.ajax({
                    url: '{{ route("reports.sales.data") }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        vendor_id: restaurant,
                        driver_id: driver,
                        customer_id: customer,
                        category_id: category,
                        start_date: start_date.toISOString().slice(0,19).replace('T',' '),
                        end_date: end_date.toISOString().slice(0,19).replace('T',' ')
                    }
                }).done(function(resp){
                    var rows = (resp && resp.rows) ? resp.rows : [];
                    if (rows.length){
                        var reportData = rows.map(mapRow);
                        generateReport(reportData, headers, fileFormat);
                    } else {
                        $(".error_top").show().html("<p>{{trans('lang.not_found_data_error')}}</p>");
                    }
                }).fail(function(xhr){
                    $(".error_top").show().text('Failed to load report');
                }).always(function(){
                    jQuery("#overlay").hide();
                    setDate();
                    $('.file_format').val('').trigger('change');
                    $('.driver').val('').trigger('change');
                    $('.customer').val('').trigger('change');
                    $('.service').val('').trigger('change');
                    $('.status').val('').trigger('change');
                    $('.payment_method').val('').trigger('change');
                    $('.payment_status').val('').trigger('change');
                });
            }
        });
    </script>
@endsection
