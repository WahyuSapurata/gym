@extends('layouts.layout')
<style>
    .custom-card-action .table-responsive .table tbody tr:last-child .btn {
        border: 1px solid;
    }

    .custom-card-action .table-responsive .table tbody tr:last-child .btn:hover {
        background-color: var(--bs-btn-hover-bg);
        border-color: var(--bs-btn-hover-border-color);
    }
</style>
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Admin</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item text-capitalize">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items ">
                    <div class="d-flex d-md-none"><a class="page-header-right-close-toggle" href="/widgets/tables"><svg
                                stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round" class="me-2" height="16" width="16"
                                xmlns="http://www.w3.org/2000/svg">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg><span>Back</span></a></div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="#" id="openModal" class="btn btn-primary"><svg stroke="currentColor" fill="none"
                                stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"
                                class="me-2" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg><span>Tambah Data</span></a>
                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center"><a class="page-header-right-open-toggle"
                        href="/widgets/tables"><svg stroke="currentColor" fill="none" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="fs-20" height="1em"
                            width="1em" xmlns="http://www.w3.org/2000/svg">
                            <line x1="21" y1="10" x2="7" y2="10"></line>
                            <line x1="21" y1="6" x2="3" y2="6"></line>
                            <line x1="21" y1="14" x2="3" y2="14"></line>
                            <line x1="21" y1="18" x2="7" y2="18"></line>
                        </svg></a></div>
            </div>
        </div>
        <div class="main-content">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card stretch stretch-full widget-tasks-content  ">
                        <div class="card-header">
                            <h5 class="card-title">Tabel {{ $module }}</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" aria-label="Refresh" data-bs-original-title="Refresh">
                                        <span class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </span>
                                    </div>
                                    <div data-bs-toggle="tooltip" aria-label="Maximize/Minimize"
                                        data-bs-original-title="Maximize/Minimize"><span
                                            class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <div class="m-3">
                                    <label for="" class="form-label">Filter Data</label>
                                    <select id="filter-expired" class="form-select" style="width:200px;">
                                        <option value="">Semua</option>
                                        <option value="7">Expired 7 Hari</option>
                                        <option value="2">Expired 2 Hari</option>
                                        <option value="0">Expired</option>
                                    </select>
                                </div>
                                <table style="width: 100%" id="dataTables" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-capitalize">No</th>
                                            <th class="text-capitalize">member</th>
                                            <th class="text-capitalize">paket</th>
                                            <th class="text-capitalize">durasi</th>
                                            <th class="text-capitalize">sesi</th>
                                            <th class="text-capitalize">tanggal expired</th>
                                            <th class="text-capitalize">bukti</th>
                                            <th class="text-capitalize">status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    <!-- Modal Form -->
    <div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form" enctype="multipart/form-data">
                <input type="hidden" name="uuid" id="uuid">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form {{ $module }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">nama member</label>
                            <select id="uuid_member" name="uuid_member" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value="">-- Pilih nama member --</option>
                                @foreach ($member as $m)
                                    <option value="{{ $m->uuid }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="text-capitalize form-label">nama paket</label>
                            <select id="uuid_paket" name="uuid_paket" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value="">-- Pilih nama paket --</option>
                                @foreach ($paket as $p)
                                    <option value="{{ $p->uuid }}">{{ $p->nama_paket }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="text-capitalize form-label">jenis pembayaran</label>
                            <select id="jenis_pembayaran" name="jenis_pembayaran" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                <option value="Tunai">Tunai</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Debit">Debit</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">keterangan</label>
                            <textarea class="form-control" name="keterangan" cols="30" rows="4"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">Bukti</label>
                            <div class="wd-100 ht-100 position-relative overflow-hidden border border-gray-2 rounded">
                                <img src="{{ asset('assets/images/logo-abbr.png') }}"
                                    class="upload-pic img-fluid rounded h-100 w-100" alt="">
                                <div
                                    class="position-absolute start-50 top-50 end-0 bottom-0 translate-middle h-100 w-100 hstack align-items-center justify-content-center c-pointer upload-button">
                                    <i class="feather feather-camera" aria-hidden="true"></i>
                                </div>
                                <input class="file-upload" type="file" name="bukti" accept="image/*">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-perpanjang" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="modal-edit-perpanjangLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-edit-perpanjang" enctype="multipart/form-data">
                <input type="hidden" name="uuid" id="uuid-edit-perpanjangan">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Perpanjangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="text" name="tanggal_mulai" id="tanggal_mulai"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Tanggal Expired</label>
                            <input type="text" name="tanggal_expired" id="tanggal_expired"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Nama Paket</label>
                            <select id="uuid_paket" name="uuid_paket" class="form-select basic-usage">
                                <option value="">-- Pilih nama paket --</option>
                                @foreach ($paket as $p)
                                    <option value="{{ $p->uuid }}">{{ $p->nama_paket }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Jenis Pembayaran</label>
                            <select id="jenis_pembayaran" name="jenis_pembayaran" class="form-select basic-usage">
                                <option value="">-- Pilih --</option>
                                <option value="Tunai">Tunai</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Debit">Debit</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="modal-edit" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="modal-editLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-edit" enctype="multipart/form-data">
                <input type="hidden" name="uuid" id="uuid-edit">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Edit Tanggal Expired</h5>
                        <button type="button" class="btn-close" id="btn-close-edit" data-bs-dismiss="modal-edit"
                            aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">tanggal mulai</label>
                            <input type="text" name="tanggal_mulai" id="tanggal_mulai_expired"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">tanggal expired</label>
                            <input type="text" name="tanggal_expired" id="tanggal_expired_expired"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal-pembayaran" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="modal-pembayaranLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-pembayaran" enctype="multipart/form-data">
                <input type="hidden" name="uuid" id="uuid-pembayaran">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Edit Tanggal Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="text-capitalize form-label">tanggal mulai</label>
                            <input type="text" name="tanggal_pembayaran" id="tanggal_pembayaran"
                                class="form-control dateofBirth">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function initSelect2(element) {
            element.select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: element.data('placeholder'),
                dropdownParent: element.closest('.modal-body')
            });
        }

        // Init select2 pertama kali
        $('.basic-usage').each(function() {
            initSelect2($(this));
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".dateofBirth").forEach(function(el) {
                new Datepicker(el, {
                    format: "dd-mm-yyyy",
                    autohide: true,
                    clearBtn: true
                });
            });
        });

        function formatRupiah(angka) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah ? 'Rp ' + rupiah : '';
        }

        // Pasang CSRF token untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#openModal').on('click', function() {
            // Buka modal
            $('#modal').modal('show');
            // Bersihkan form
            $('#form')[0].reset();
            $('#uuid').val('');

            // Reset semua input dan select di seluruh form
            // $('#form').find('input').val('');
            $('#form').find('select').val('');

            // Kalau pakai select2, reset juga semua select2 di form
            $('#form').find('select').each(function() {
                $(this).val('').trigger('change');
            });

            // reset preview & file upload
            $('.upload-pic').attr('src', "{{ asset('assets/images/logo-abbr.png') }}");
            $('.file-upload').val('');

            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Submit Form (Tambah / Edit)
        $('#form').on('submit', function(e) {
            e.preventDefault();

            let uuid = $('#uuid').val();

            let updateUrl = `{{ route('admin.transaksi-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('admin.transaksi-store') }}`;
            let method = uuid ? 'POST' : 'POST';

            let formData = new FormData(this);

            $.ajax({
                url: url,
                method: method,
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    Swal.fire({
                        title: "Sukses",
                        text: res.message,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    // Bersihkan error lama
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Tutup modal
                    $('#modal').modal('hide');

                    // Refresh datatable
                    $('#dataTables').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Error validasi Laravel
                        let errors = xhr.responseJSON.errors;

                        // Bersihkan error lama
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').remove();

                        // Loop semua error
                        $.each(errors, function(field, messages) {
                            let input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');

                            // Tambahkan feedback di bawah input
                            input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                        });
                    } else {
                        Swal.fire({
                            title: "Eror",
                            text: xhr.responseJSON.message || "Terjadi kesalahan",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1500,
                        });
                    }
                }
            });
        });

        // Edit
        // $('#dataTables').on('click', '.edit', function() {
        //     // Buka modal
        //     // Hapus error lama
        //     $('.is-invalid').removeClass('is-invalid');
        //     $('.invalid-feedback').remove();
        //     $('#modal').modal('show');
        //     let uuid = $(this).data('uuid');
        //     let editUrl = `{{ route('admin.transaksi-edit', ':uuid') }}`;
        //     editUrl = editUrl.replace(':uuid', uuid);
        //     $.get(editUrl, function(res) {
        //         $.each(res, function(key, value) {
        //             let $field = $(`[name="${key}"]`);

        //             if (!$field.length) return;

        //             let type = $field.attr('type');
        //             const typeRadio = $(`[name="${key}"]`).attr('type');

        //             if ($field.hasClass('select2-hidden-accessible')) {
        //                 $field.val(value).trigger('change');
        //             }
        //             // Datepicker
        //             else if ($field.hasClass('datepicker')) {
        //                 $field.datepicker('update', value);
        //             } else if (typeRadio === 'radio' || typeRadio === 'checkbox') {
        //                 $(`[name="${key}"]`).prop('checked', false);
        //                 if (value) {
        //                     $(`[name="${key}"][value="${value}"]`).prop('checked', true);
        //                 }
        //             }
        //             // File
        //             else if (type === 'file') {
        //                 // Jika ada file, tampilkan preview
        //                 if (value) {
        //                     $field.closest('.mb-2').find('.upload-pic')
        //                         .attr('src', `{{ asset('storage') }}/${value}`);
        //                 } else {
        //                     $field.closest('.mb-2').find('.upload-pic')
        //                         .attr('src', '{{ asset('assets/images/logo-abbr.png') }}');
        //                 }
        //             } else if (key === 'harga') {
        //                 // Kalau harga, format ke Rupiah saat set value
        //                 $(`[name="${key}"]`).val(formatRupiah(value.toString()));
        //             }
        //             // Default
        //             else {
        //                 $field.val(value);
        //             }
        //         });
        //     });
        // });

        $('#dataTables').on('click', '.konfirmasi', function() {
            let uuid = $(this).data('uuid');

            $.ajax({
                url: '/admin/konfirmasi-transaksi/' + uuid,
                type: 'GET',
                success: function(res) {
                    Swal.fire({
                        title: "Sukses",
                        text: res.message,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    $('#dataTables').DataTable().ajax.reload(null, false);
                },
                error: function(err) {
                    Swal.fire({
                        title: "Error",
                        text: "Gagal mengkonfirmasi transaksi.",
                        icon: "error",
                    });
                }
            });
        });

        $('#dataTables').on('click', '.cancel', function() {
            let uuid = $(this).data('uuid');

            $.ajax({
                url: '/admin/cancel-transaksi/' + uuid,
                type: 'GET',
                success: function(res) {
                    Swal.fire({
                        title: "Sukses",
                        text: res.message,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    $('#dataTables').DataTable().ajax.reload(null, false);
                },
                error: function(err) {
                    Swal.fire({
                        title: "Error",
                        text: "Gagal membatalkan transaksi.",
                        icon: "error",
                    });
                }
            });
        });

        // Klik tombol edit
        $('#dataTables').on('click', '.edit', function() {
            let uuid = $(this).data('uuid');
            $('#uuid-edit').val(uuid);

            $.ajax({
                url: '/admin/get-tanggal-expired/' + uuid,
                type: 'GET',
                success: function(res) {
                    $('#tanggal_mulai_expired').val(res.data.mulai);
                    $('#tanggal_expired_expired').val(res.data.expired_at);
                    $('#modal-edit').modal('show');
                }
            });
        });

        $('#form-edit').on('submit', function(e) {
            e.preventDefault();
            let uuid = $('#uuid-edit').val();

            $.ajax({
                url: '/admin/edit-tanggal-expired/' + uuid,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: "Berhasil",
                        text: res.message,
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#modal-edit').modal('hide');
                    $('#dataTables').DataTable().ajax.reload();
                }
            });
        });

        // Klik tombol edit
        $('#dataTables').on('click', '.perpanjang-member', function() {
            let uuid = $(this).data('uuid');

            $('#uuid-edit-perpanjangan').val(uuid);

            // $.ajax({
            //     url: '/admin/get-perpanjang-data/' + uuid,
            //     type: 'GET',
            //     success: function(res) {
            //         console.log(res);

            //         $('#tanggal_mulai').val(res.data.mulai);
            //         $('#tanggal_expired').val(res.data.expired_at);
            //         $('#uuid_paket').val(res.data.uuid_paket).trigger('change');
            //         $('#jenis_pembayaran').val(res.data.jenis_pembayaran).trigger('change');

            $('#modal-edit-perpanjang').modal('show');
            //     }
            // });
        });

        $('#form-edit-perpanjang').on('submit', function(e) {
            e.preventDefault(); // hindari reload default

            let uuid = $('#uuid-edit-perpanjangan').val();

            $.ajax({
                url: '/admin/perpanjang-member/' + uuid,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: "Berhasil",
                        text: res.message,
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#modal-edit-perpanjang').modal('hide');
                    $('#dataTables').DataTable().ajax.reload();
                }
            });
        });

        // Klik tombol edit
        $('#dataTables').on('click', '.pembayaran-edit', function() {
            let uuid = $(this).data('uuid');

            $('#uuid-pembayaran').val(uuid);
            $('#modal-pembayaran').modal('show');
        });

        $('#form-pembayaran').on('submit', function(e) {
            e.preventDefault(); // hindari reload default

            let uuid = $('#uuid-pembayaran').val();

            $.ajax({
                url: '/admin/edit-tanggal-pembayaran/' + uuid,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: "Berhasil",
                        text: res.message,
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#modal-pembayaran').modal('hide');
                    $('#dataTables').DataTable().ajax.reload();
                }
            });
        });

        $('#dataTables').on('click', '.cetak', function() {
            let uuid = $(this).data('uuid');
            window.open('/admin/cetak-invoice/' + uuid, '_blank');
        });

        $('#dataTables').on('click', '.cetak-kartu', function() {
            let uuid = $(this).data('uuid');
            window.open('/admin/cetak-kartu/' + uuid, '_blank');
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('admin.transaksi-delete', ':uuid') }}`;
            deleteUrl = deleteUrl.replace(':uuid', uuid);

            Swal.fire({
                title: 'Yakin hapus?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: "Sukses",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1500,
                            });
                            $('#dataTables').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Gagal",
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.img-clickable', function() {
            const fullImage = $(this).data('full');
            Swal.fire({
                imageUrl: fullImage,
                imageAlt: 'Preview Bukti',
                showConfirmButton: false,
                background: '#000',
                width: 'auto',
                padding: 0,
            });
        });

        $(document).on('click', '#btn-close-edit', function() {
            $('#modal-edit').modal('hide');
        });

        const initDatatable = () => {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#dataTables')) {
                $('#dataTables').DataTable().clear().destroy();
            }

            $('#dataTables').DataTable({
                responsive: true,
                pageLength: 10,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.transaksi-get') }}",
                    data: function(d) {
                        d.filter_expired = $('#filter-expired').val(); // ⬅ kirim filter
                    }
                },
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nama_member',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'nama_paket',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'durasi_hari',
                        class: 'mb-kolom-text text-left align-content-center',
                        render: function(data, type, row) {
                            // Format durasi hari ke angka
                            return data ? data + ' Hari' :
                                '<span class="text-muted">Tidak ada durasi</span>';
                        }
                    },
                    {
                        data: 'remaining_session',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            // Format total sesi ke angka
                            return data ? data + ' Sesi' :
                                '<span class="text-muted">Tidak ada sesi</span>';
                        }

                    },
                    {
                        data: 'expired_at',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            if (!data) return `<span class="badge bg-secondary">-</span>`;

                            // Parsing format d-m-Y → Date object
                            const parts = data.split('-'); // contoh: ["12", "11", "2025"]
                            const expiredDate = new Date(parts[2], parts[1] - 1, parts[
                                0]); // (tahun, bulan-1, tanggal)

                            const today = new Date();
                            // Hilangkan jam agar perhitungan hari lebih akurat
                            today.setHours(0, 0, 0, 0);
                            expiredDate.setHours(0, 0, 0, 0);

                            const diffTime = expiredDate - today;
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                            let badgeClass = "bg-success"; // default warna hijau
                            let label = data;

                            if (diffDays <= 7 && diffDays > 2) {
                                badgeClass = "bg-warning text-dark"; // kuning
                            } else if (diffDays <= 2 && diffDays >= 0) {
                                badgeClass = "bg-danger"; // merah
                            } else if (diffDays < 0) {
                                badgeClass = "bg-secondary"; // abu-abu (expired)
                                label += " (expired)";
                            }

                            return `<span class="badge text-uppercase ${badgeClass}">
                                        ${label}
                                </span>`;
                        }
                    },
                    {
                        data: 'bukti',
                        render: function(data, type, row) {
                            if (data) {
                                const imageUrl = `{{ asset('storage') }}/${data}`;
                                return `
                                    <img src="${imageUrl}"
                                        class="img-thumbnail img-clickable"
                                        style="max-width: 50px; cursor:pointer;"
                                        data-full="${imageUrl}">
                                `;
                            }
                            return '<span class="text-muted">Tidak ada foto</span>';
                        },
                    },
                    {
                        data: 'status',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return `
                                <span class="badge text-uppercase bg-${data === 'paid' ? 'warning' : data === 'cancelled' ? 'danger' : 'success'}">
                                    ${data}
                                </span>
                            `;
                        }
                    },
                    {
                        data: 'uuid', // akan diganti di columnDefs
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [{
                    targets: -1, // kolom terakhir
                    title: 'Aksi',
                    class: 'mb-kolom-aksi text-end',
                    render: function(data, type, row) {
                        let urlKonfirmasi =
                            "{{ route('admin.konfirmasi-transaksi', ['params' => ':uuid']) }}";
                        urlKonfirmasi = urlKonfirmasi.replace(':uuid', data);

                        let urlCancel =
                            "{{ route('admin.cancel-transaksi', ['params' => ':uuid']) }}";
                        urlCancel = urlCancel.replace(':uuid', data);
                        return `
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-success konfirmasi btn-sm">
                                        Konfirmasi
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-danger cancel btn-sm">
                                        Cancel
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-info cetak btn-sm">
                                        Cetak Invoice
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-warning cetak-kartu btn-sm">
                                        Cetak Kartu
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-secondary edit btn-sm">
                                        Edit Tanggal Expired
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-success perpanjang-member btn-sm">
                                        Perpanjang Member
                                    </a>
                                    <a href="#" data-uuid="${data}" class="btn btn-outline-info pembayaran-edit btn-sm">
                                        Edit Tanggal Pembayaran
                                    </a>
                                    <a href="#" class="avatar-text avatar-md delete" data-uuid="${data}">
                                        <!-- Icon Delete -->
                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                            viewBox="0 0 24 24" stroke-linecap="round"
                                            stroke-linejoin="round" height="1em" width="1em">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4
                                                a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </a>
                                </div>
                    `;
                    }
                }]
            });
        };

        $(function() {
            // ketika dropdown berubah → reload tabel
            $('#filter-expired').on('change', function() {
                $('#dataTables').DataTable().ajax.reload();
            });

            initDatatable();
        });
    </script>
@endpush
