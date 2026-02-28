@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row page-titles">
                <div class="col-md-6 align-self-center">
                    <h3 class="text-themecolor">
                        Wallet Coins Transactions
                        <span class="userTitle"></span>
                    </h3>
                </div>
                <div class="col-md-6 align-self-center text-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Wallet Coins</li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/coins.png') }}"></span>
                            <h3 class="mb-0">Wallet Coins Transactions</h3>
                            <span class="counter ml-3 total_count">0</span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="walletCoinsTable"
                               class="table table-bordered table-striped table-hover"
                               width="100%">
                            <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Type</th>
                                <th>Coins</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script>
        const userId = "{{ $id }}";

        $(document).ready(function () {

            const table = $('#walletCoinsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 30,
                lengthMenu: [[10, 25, 30, 50, 100, -1], [10, 25, 30, 50, 100]],


                // ✅ Correct column index (Date = index 3)
                order: [[3, 'desc']],

                ajax: {
                    url: "{{ route('users.wallet_coins_transaction', $id) }}",
                    type: "GET",
                    data: function (d) {
                        d.user_id = userId;
                    }
                },

                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'type',
                        render: function (data) {
                            if (data === 'REWARDED') {
                                return `<span class="badge reward-badge">🏆 ${data} </span>`;
                            }
                            return `<span class="">${data}</span>`;
                        }
                    },
                    {
                        data: 'coins',
                        render: function (data, type, row) {
                            if (row.type === 'REWARDED') {
                                return `<span class="text-green">+  ${data}</span>`;
                            }
                            if(row.type === 'REDEEM_DEBIT'){
                                return `<span class="text-red">${data}</span>`
                            }
                            return `<span>${data}</span>`;
                        }
                    },
                    { data: 'formattedDate' }
                ],

                drawCallback: function (settings) {
                    if (settings.json) {
                        $('.total_count').text(settings.json.recordsTotal);
                    }
                },

                language: {
                    emptyTable: "No wallet coin transactions found",
                    zeroRecords: "No matching records found",
                    "processing": ""
                }
            });

        });
    </script>
@endsection
