@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
    .table-active { background-color: rgba(0, 0, 0, 0.05); }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; }
    .sort.cursor-pointer:hover { background-color: #f5f5f5; }
    .form-control.teacher-comment-input, .form-control.guidance-comment-input { width: 100%; min-width: 150px; }
    .btn-primary { margin-top: 1rem; }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Class Broadsheet</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Class Broadsheet</a></li>
                                <li class="breadcrumb-item active">Class Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error and Success Messages -->
            @if ($errors->any())
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('status') || session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') ?: session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <!-- Class Broadsheet Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Broadsheet for {{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <div class="d-flex flex-column flex-grow-1">
                                            <div class="d-flex flex-wrap">
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold">{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $schoolterm }} | {{ $schoolsession }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm bg-white">
                                        <form id="commentsForm" action="{{ route('classbroadsheet.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="search-box mb-3">
                                                <input type="text" class="form-control search" placeholder="Search students, admission no, or comments...">
                                            </div>
                                            <div class="mt-3 result-table">
                                                <div id="studentListTable" class="table-responsive">
                                                    <table class="table table-centered align-middle table-nowrap mb-0">
                                                        <thead class="table-active">
                                                            <tr>
                                                                <th class="sort cursor-pointer" data-sort="sn">SN</th>
                                                                <th class="sort cursor-pointer" data-sort="admissionno">Admission No</th>
                                                                <th class="sort cursor-pointer" data-sort="name">Student Name</th>
                                                                <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                                {{-- @foreach ($subjects as $subject)
                                                                    <th class="sort cursor-pointer" data-sort="subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}">{{ $subject->subject }}</th>
                                                                @endforeach --}}
                                                                <th class="sort cursor-pointer" data-sort="teacher-comment">Class Teacher's Comment</th>
                                                                <th class="sort cursor-pointer" data-sort="guidance-comment">Guidance Counselor's Comment</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="list">
                                                            @forelse ($students as $key => $student)
                                                                @php
                                                                    $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                                    $imagePath = asset('storage/student_avatars/' . $picture);
                                                                    $fileExists = file_exists(storage_path('app/public/student_avatars/' . $picture));
                                                                    $defaultImageExists = file_exists(storage_path('app/public/student_avatars/unnamed.jpg'));
                                                                    $profile = $personalityProfiles->where('studentid', $student->id)->first();
                                                                @endphp
                                                                <tr>
                                                                    <td class="sn">{{ $key + 1 }}</td>
                                                                    <td class="admissionno" data-admissionno="{{ $student->admissionNo }}">{{ $student->admissionNo }}</td>
                                                                    <td class="name" data-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="{{ $imagePath }}"
                                                                                 alt="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}"
                                                                                 class="rounded-circle avatar-sm student-image"
                                                                                 data-bs-toggle="modal"
                                                                                 data-bs-target="#imageViewModal"
                                                                                 data-image="{{ $imagePath }}"
                                                                                 data-admissionno="{{ $student->admissionNo }}"
                                                                                 data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                                 data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.log('Table image failed to load for admissionno: {{ $student->admissionNo ?? 'unknown' }}, picture: {{ $student->picture ?? 'none' }}');" />
                                                                            <div class="ms-3">
                                                                                <h6 class="mb-0">
                                                                                    <a href="{{ route('myclass.studentpersonalityprofile', [$student->id, $schoolclassid, $termid, $sessionid]) }}"
                                                                                       class="text-reset">
                                                                                        {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                                    </a>
                                                                                </h6>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="gender" data-gender="{{ $student->gender ?? 'N/A' }}">{{ $student->gender ?? 'N/A' }}</td>
                                                                    @foreach ($subjects as $subject)
                                                                        @php
                                                                            $score = $scores->where('student_id', $student->id)->where('subject_name', $subject->subject)->first();
                                                                        @endphp
                                                                        {{-- <td class="subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}"
                                                                            data-subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}="{{ $score ? $score->total : '-' }}"
                                                                            align="center" style="font-size: 14px;"
                                                                            @if ($score && is_numeric($score->total) && $score->total <= 50) class="highlight-red" @endif>
                                                                            {{ $score ? $score->total : '-' }}
                                                                        </td> --}}
                                                                    @endforeach
                                                                    <td class="teacher-comment">
                                                                        <input type="text" class="form-control teacher-comment-input"
                                                                               name="teacher_comments[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->classteachercomment : '' }}"
                                                                               data-teacher-comment="{{ $profile ? $profile->classteachercomment : 'N/A' }}"
                                                                               placeholder="Enter teacher's comment">
                                                                    </td>
                                                                    <td class="guidance-comment">
                                                                        <input type="text" class="form-control guidance-comment-input"
                                                                               name="guidance_comments[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->guidancescomment : '' }}"
                                                                               data-guidance-comment="{{ $profile ? $profile->guidancescomment : 'N/A' }}"
                                                                               placeholder="Enter guidance counselor's comment">
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="{{ 7 + count($subjects) }}" class="noresult" style="display: block;">
                                                                        <div class="text-center">
                                                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                                                       colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                                                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                                                            <p class="text-muted mb-0">We've searched for the student data but did not find any matches.</p>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <div class="pagination-wrap hstack gap-2">
                                                            <span>Showing <span id="pagination-showing">0</span> of <span id="pagination-total">0</span> entries</span>
                                                            <ul class="pagination listjs-pagination mb-0"></ul>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <button type="submit" class="btn btn-primary">Save Comments</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image View Modal -->
                <div class="row">
                    <div class="col-12">
                        <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Student Picture</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img id="enlargedImage" src="" alt="Student Picture" class="img-fluid" />
                                        <div class="placeholder-text">No image available</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            No student data found for this class, term, and session.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Inline Script to Pass Subjects to JavaScript -->
<script>
    window.subjects = [
        @foreach ($subjects as $subject)
            '{{ \Illuminate\Support\Str::slug($subject->subject) }}',
        @endforeach
    ];
</script>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Show content after DOM is loaded to prevent FOUC
        document.querySelector('.main-content').classList.add('loaded');

        const imageViewModal = document.getElementById('imageViewModal');
        if (imageViewModal) {
            imageViewModal.addEventListener('show.bs.modal', async function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image') || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                const admissionNo = button.getAttribute('data-admissionno') || 'unknown';
                const fileExists = button.getAttribute('data-file-exists') === 'true';
                const defaultImageExists = button.getAttribute('data-default-exists') === 'true';
                const modalImage = this.querySelector('#enlargedImage');
                const placeholderText = this.querySelector('.placeholder-text');

                console.log(`Opening image modal for admissionNo: ${admissionNo}, src: ${imageSrc}, fileExists: ${fileExists}, defaultImageExists: ${defaultImageExists}`);

                modalImage.src = '';
                modalImage.style.display = 'none';
                placeholderText.style.display = 'none';

                if (!fileExists) {
                    console.log(`Server-side check indicates image does not exist for admissionNo: ${admissionNo}`);
                    modalImage.src = '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                    if (defaultImageExists) {
                        modalImage.style.display = 'block';
                    } else {
                        console.error(`Default image does not exist for admissionNo: ${admissionNo}`);
                        placeholderText.textContent = `No image available for ${admissionNo}`;
                        placeholderText.style.display = 'block';
                    }
                } else {
                    const imageExists = await checkImageExists(imageSrc);
                    if (imageExists) {
                        modalImage.src = imageSrc;
                        modalImage.style.display = 'block';
                    } else {
                        console.error(`Image does not exist for admissionNo: ${admissionNo}, attempted URL: ${imageSrc}`);
                        modalImage.src = '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                        if (defaultImageExists) {
                            modalImage.style.display = 'block';
                            placeholderText.style.display = 'none';
                        } else {
                            console.error(`Default image failed to load for admissionNo: ${admissionNo}`);
                            placeholderText.textContent = `No image available for ${admissionNo}`;
                            placeholderText.style.display = 'block';
                        }
                    }
                }

                modalImage.onload = () => {
                    console.log(`Successfully loaded image for admissionNo: ${admissionNo}, src: ${modalImage.src}`);
                    modalImage.style.display = 'block';
                    placeholderText.style.display = 'none';
                };

                modalImage.onerror = () => {
                    console.error(`Failed to load image for admissionNo: ${admissionNo}, attempted URL: ${imageSrc}`);
                    modalImage.src = '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                    if (defaultImageExists) {
                        modalImage.style.display = 'block';
                        placeholderText.style.display = 'none';
                    } else {
                        placeholderText.textContent = `No image available for ${admissionNo}`;
                        placeholderText.style.display = 'block';
                    }
                };
            });

            imageViewModal.addEventListener('hidden.bs.modal', function () {
                const modalImage = this.querySelector('#enlargedImage');
                const placeholderText = this.querySelector('.placeholder-text');
                modalImage.src = '';
                modalImage.style.display = 'none';
                placeholderText.textContent = '';
                placeholderText.style.display = 'none';
                console.log('imageViewModal closed, cleared image src and placeholder text');
            });
        }

        function checkImageExists(url) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    console.log(`Image check succeeded for URL: ${url}`);
                    resolve(true);
                };
                img.onerror = () => {
                    console.log(`Image check failed for URL: ${url}`);
                    resolve(false);
                };
                img.src = url;
            });
        }
    });
</script>
@endsection