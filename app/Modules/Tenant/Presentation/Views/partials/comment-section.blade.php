<div class="comments-section p-4">
    {{-- Add Comment Form --}}
    <form action="{{ route('tenant.comments.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <input type="hidden" name="related_to" value="{{ $recordId }}">

        <div class="mb-3">
            <textarea name="commentcontent" class="form-control rounded-4 shadow-sm" rows="3"
                placeholder="{{ __('tenant::tenant.write_comment_placeholder') ?? 'Write a comment...' }}"
                required></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <label class="btn btn-sm btn-soft-secondary rounded-3 cursor-pointer mb-0">
                    <i class="bi bi-paperclip me-1"></i> {{ __('tenant::tenant.attach_files') ?? 'Attach Files' }}
                    <input type="file" name="files[]" multiple class="d-none" onchange="updateFileCount(this)">
                </label>
                <small id="file-count" class="text-muted"></small>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_private" value="1" id="isPrivateSwitch">
                    <label class="form-check-label small text-muted"
                        for="isPrivateSwitch">{{ __('tenant::tenant.private_comment') }}</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary rounded-3 px-4">
                <i class="bi bi-send me-1"></i> {{ __('tenant::tenant.post_comment') ?? 'Post' }}
            </button>
        </div>
    </form>

    {{-- Comments List --}}
    <div class="comments-list mt-5">
        @php
            $commentRepo = app(\App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface::class);
            $comments = $commentRepo->getCommentsForRecord($recordId);
        @endphp

        @forelse($comments as $comment)
            <div class="comment-item mb-4 pb-4 border-bottom last-border-0">
                <div class="d-flex gap-3">
                    <div class="avatar-box">
                        <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle" width="40"
                            height="40" alt="">
                    </div>
                    <div class="comment-content flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <a href="#" class="fw-bold text-main text-decoration-none">
                                    {{ $comment->getUserName() ?: 'User #' . $comment->getUserId() }}
                                </a>
                                <small
                                    class="text-muted ms-2">{{ \Carbon\Carbon::instance($comment->getCreatedTime())->diffForHumans() }}</small>
                                @if($comment->isPrivate())
                                    <span class="badge bg-soft-warning text-warning ms-2 small rounded-pill px-2">
                                        <i class="bi bi-lock-fill me-1"></i>{{ __('tenant::tenant.private') }}
                                    </span>
                                @endif
                            </div>

                            @if($comment->getUserId() == auth('tenant')->id())
                                <div class="dropdown">
                                    <button class="btn btn-link link-secondary p-0 border-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="#"
                                                onclick="editComment({{ $comment->getId() }}, '{{ addslashes($comment->getContent()) }}')">
                                                <i class="bi bi-pencil me-2"></i>{{ __('tenant::tenant.edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider opacity-50">
                                        </li>
                                        <li>
                                            <form action="{{ route('tenant.comments.destroy', $comment->getId()) }}"
                                                method="POST"
                                                onsubmit="return confirm('{{ __('tenant::tenant.confirm_delete_comment') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bi bi-trash me-2"></i>{{ __('tenant::tenant.delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="comment-text text-muted mb-3">
                            {{ $comment->getContent() }}
                        </div>

                        {{-- Attachments --}}
                        @if(!empty($comment->getAttachments()))
                            <div class="attachments-list d-flex flex-wrap gap-2 mt-2">
                                @foreach($comment->getAttachments() as $attachment)
                                    @php
                                        $cleanPath = str_replace('storage/', '', $attachment->path);
                                    @endphp
                                    <a href="{{ tenant_asset($cleanPath) }}" target="_blank"
                                        class="attachment-chip text-decoration-none bg-light border rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <span class="small text-dark text-truncate"
                                            style="max-width: 150px;">{{ $attachment->name }}</span>
                                        <i class="bi bi-download small text-muted"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($comment->getReasonToEdit())
                            <div class="bg-light rounded-3 p-2 mt-2">
                                <small class="text-muted font-italic">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ __('tenant::tenant.edited') }}: {{ $comment->getReasonToEdit() }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-chat-dots text-muted opacity-25" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">{{ __('tenant::tenant.no_comments_yet') }}</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Edit Comment Modal --}}
<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold">{{ __('tenant::tenant.edit_comment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCommentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label
                            class="form-label fw-bold small text-muted">{{ __('tenant::tenant.comment_content') }}</label>
                        <textarea name="commentcontent" id="editCommentContent" class="form-control rounded-3" rows="4"
                            required></textarea>
                    </div>
                    <div class="mb-0">
                        <label
                            class="form-label fw-bold small text-muted">{{ __('tenant::tenant.reason_for_editing') }}</label>
                        <input type="text" name="reasontoedit" class="form-control rounded-3"
                            placeholder="{{ __('tenant::tenant.reason_placeholder') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4"
                        data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                    <button type="submit"
                        class="btn btn-primary rounded-3 px-4">{{ __('tenant::tenant.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileCount(input) {
        const space = document.getElementById('file-count');
        if (input.files.length > 0) {
            space.innerText = `${input.files.length} {{ __('tenant::tenant.files_selected') }}`;
        } else {
            space.innerText = '';
        }
    }

    function editComment(id, content) {
        const modal = new bootstrap.Modal(document.getElementById('editCommentModal'));
        const form = document.getElementById('editCommentForm');
        const textarea = document.getElementById('editCommentContent');

        // Construct the URL using a dummy and then replacing it safely
        let url = "{{ route('tenant.comments.update', ':id') }}";
        url = url.replace(':id', id);

        form.action = url;
        textarea.value = content;

        modal.show();
    }
</script>

<style>
    .last-border-0:last-child {
        border-bottom: 0 !important;
    }

    .btn-soft-secondary {
        background-color: #f1f5f9;
        color: #475569;
        border: none;
    }

    .btn-soft-secondary:hover {
        background-color: #e2e8f0;
    }

    .attachment-chip {
        transition: all 0.2s ease;
    }

    .attachment-chip:hover {
        background-color: #f1f5f9 !important;
        transform: translateY(-1px);
    }
</style>