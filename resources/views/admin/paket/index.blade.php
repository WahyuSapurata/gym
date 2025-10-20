@extends('layouts.layout')
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
                                <table style="width: 100%" id="dataTables" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-capitalize">No</th>
                                            <th class="text-capitalize">tipe member</th>
                                            <th class="text-capitalize">nama paket</th>
                                            <th class="text-capitalize">durasi hari</th>
                                            <th class="text-capitalize">total sesi</th>
                                            <th class="text-capitalize">harga</th>
                                            <th class="text-capitalize">deskripsi</th>
                                            <th class="text-capitalize">status</th>
                                            <th class="text-capitalize">gambar</th>
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
                            <label class="text-capitalize form-label">Tipe Member</label>
                            <select id="tipe_member" name="tipe_member" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value="">-- Pilih Tipe Member --</option>
                                <option value="GYM">GYM</option>
                                <option value="FUNGSIONAL">FUNGSIONAL</option>
                                <option value="STUDIO">STUDIO</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Input Durasi Hari -->
                        <div class="mb-2" id="durasi_hari_group" style="display: none;">
                            <label class="form-label">Durasi (Hari)</label>
                            <input type="number" name="durasi_hari" id="durasi_hari" class="form-control"
                                placeholder="Masukkan durasi dalam hari">
                        </div>

                        <!-- Input Total Sesi -->
                        <div class="mb-2" id="total_sesi_group" style="display: none;">
                            <label class="form-label">Total Sesi</label>
                            <input type="number" name="total_sesi" id="total_sesi" class="form-control"
                                placeholder="Masukkan total sesi">
                        </div>

                        <div class="mb-2">
                            <label class="text-capitalize form-label">nama paket</label>
                            <input type="text" name="nama_paket" id="nama_paket" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">harga</label>
                            <input type="text" name="harga" id="harga" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">status</label>
                            <select id="status" name="status" data-placeholder="Pilih inputan"
                                class="form-select basic-usage">
                                <option value=""></option>
                                <option value="Aktiv">Aktiv</option>
                                <option value="Non AKtiv">Non AKtiv</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">deskripsi</label>
                            <textarea class="form-control" name="deskripsi" cols="30" rows="4"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label class="text-capitalize form-label">Foto</label>
                            <div class="wd-100 ht-100 position-relative overflow-hidden border border-gray-2 rounded">
                                <img src="{{ asset('assets/images/logo-abbr.png') }}"
                                    class="upload-pic img-fluid rounded h-100 w-100" alt="">
                                <div
                                    class="position-absolute start-50 top-50 end-0 bottom-0 translate-middle h-100 w-100 hstack align-items-center justify-content-center c-pointer upload-button">
                                    <i class="feather feather-camera" aria-hidden="true"></i>
                                </div>
                                <input class="file-upload" type="file" name="gambar" accept="image/*">
                            </div>
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

        // Event ketika tipe member berubah
        $('#tipe_member').on('change', function() {
            const value = $(this).val();

            // Sembunyikan semua dulu
            $('#durasi_hari_group').hide();
            $('#total_sesi_group').hide();

            // Tampilkan sesuai tipe
            if (value === 'GYM') {
                $('#durasi_hari_group').show();
            } else if (value === 'FUNGSIONAL' || value === 'STUDIO') {
                $('#total_sesi_group').show();
            }
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

        $('#harga').on('input', function() {
            $(this).val(formatRupiah(this.value));
        });

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

            let updateUrl = `{{ route('admin.paket-update', ':uuid') }}`;
            updateUrl = updateUrl.replace(':uuid', uuid);

            let url = uuid ? updateUrl :
                `{{ route('admin.paket-store') }}`;
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
        $('#dataTables').on('click', '.edit', function() {
            // Buka modal
            // Hapus error lama
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#modal').modal('show');
            let uuid = $(this).data('uuid');
            let editUrl = `{{ route('admin.paket-edit', ':uuid') }}`;
            editUrl = editUrl.replace(':uuid', uuid);
            $.get(editUrl, function(res) {
                $.each(res, function(key, value) {
                    let $field = $(`[name="${key}"]`);

                    if (!$field.length) return;

                    let type = $field.attr('type');
                    const typeRadio = $(`[name="${key}"]`).attr('type');

                    if ($field.hasClass('select2-hidden-accessible')) {
                        $field.val(value).trigger('change');
                    }
                    // Datepicker
                    else if ($field.hasClass('datepicker')) {
                        $field.datepicker('update', value);
                    } else if (typeRadio === 'radio' || typeRadio === 'checkbox') {
                        $(`[name="${key}"]`).prop('checked', false);
                        if (value) {
                            $(`[name="${key}"][value="${value}"]`).prop('checked', true);
                        }
                    }
                    // File
                    else if (type === 'file') {
                        // Jika ada file, tampilkan preview
                        if (value) {
                            $field.closest('.mb-2').find('.upload-pic')
                                .attr('src', `{{ asset('storage') }}/${value}`);
                        } else {
                            $field.closest('.mb-2').find('.upload-pic')
                                .attr('src', '{{ asset('assets/images/logo-abbr.png') }}');
                        }
                    } else if (key === 'harga') {
                        // Kalau harga, format ke Rupiah saat set value
                        $(`[name="${key}"]`).val(formatRupiah(value.toString()));
                    }
                    // Default
                    else {
                        $field.val(value);
                    }
                });
            });
        });

        // Hapus
        $('#dataTables').on('click', '.delete', function() {
            let uuid = $(this).data('uuid');
            let deleteUrl = `{{ route('admin.paket-delete', ':uuid') }}`;
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
                ajax: "{{ route('admin.paket-get') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'tipe_member',
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
                        data: 'total_sesi',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            // Format total sesi ke angka
                            return data ? data + ' Sesi' :
                                '<span class="text-muted">Tidak ada sesi</span>';
                        }

                    },
                    {
                        data: 'harga',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            // Format jumlah ke Rupiah
                            return formatRupiah(data.toString());
                        }
                    },
                    {
                        data: 'deskripsi',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            // Format jumlah ke Rupiah
                            return data ? data :
                                '<span class="text-muted">Tidak ada deskripsi</span>';
                        }
                    },
                    {
                        data: 'status',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row) {
                            return `
                                <span class="badge text-uppercase bg-${data === 'Aktiv' ? 'success' : 'danger'}">
                                    ${data}
                                </span>
                            `;
                        }
                    },
                    {
                        data: 'gambar',
                        render: function(data, type, row) {
                            if (data) {
                                return `<img src="{{ asset('storage') }}/${data}" class="img-fluid rounded" style="max-width: 50px;">`;
                            }
                            return '<span class="text-muted">Tidak ada foto</span>';
                        },
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
                        return `
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="#" class="avatar-text avatar-md edit" data-uuid="${data}">
                                        <!-- Icon Edit -->
                                        <svg stroke="currentColor" fill="none" stroke-width="2"
                                            viewBox="0 0 24 24" stroke-linecap="round"
                                            stroke-linejoin="round" height="1em" width="1em">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
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
            initDatatable();
        });
    </script>
@endpush
