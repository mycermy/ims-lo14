<div class="d-inline-block">
    <!-- Button trigger Discount Modal -->
    <span
        wire:click="$dispatch('discountModalRefresh', { product_id: {{ $cart_item->id }}, row_id: '{{ $cart_item->rowId }}' })"
        role="button" class="badge badge-warning pointer-event" data-bs-toggle="modal"
        data-bs-target="#discountModal{{ $cart_item->id }}">
        <i class="bi bi-pencil-square text-dark"></i>
    </span>
</div>
<!-- Discount Modal -->
<div wire:ignore.self class="modal fade" role="dialog" id="discountModal{{ $cart_item->id }}"
    data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="discountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">
                    {{ $cart_item->name }}
                    <br>
                    <span class="badge text-bg-success">
                        {{ $cart_item->options->code }}
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body layout-wrapper">
                <fieldset class="mb-3">
                    @if (session()->has('discount_message' . $cart_item->id))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="alert-body">
                                <span>{{ session('discount_message' . $cart_item->id) }}</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column gap-3">
                        <div class="form-group row row-cols-sm-2 align-items-baseline">
                            <label class="col-sm-3 text-wrap form-label">Discount Type <span
                                    class="text-danger">*</span></label>
                            <div class="col col-md-8">
                                <select wire:model.live="discount_type.{{ $cart_item->id }}" class="form-control"
                                    required>
                                    <option value="fixed">Fixed</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row row-cols-sm-2 align-items-baseline">
                            @if ($discount_type[$cart_item->id] == 'percentage')
                                <label class="col-sm-3 text-wrap form-label">Discount(%) <span
                                        class="text-danger">*</span></label>
                                <div class="col col-md-8">
                                    <input wire:model="item_discount.{{ $cart_item->id }}" type="number"
                                        class="form-control" value="{{ $item_discount[$cart_item->id] }}" min="0"
                                        max="100">
                                </div>
                            @elseif($discount_type[$cart_item->id] == 'fixed')
                                <label class="col-sm-3 text-wrap form-label">Discount <span
                                        class="text-danger">*</span></label>
                                <div class="col col-md-8">
                                    <input wire:model="item_discount.{{ $cart_item->id }}" type="number"
                                        class="form-control" value="{{ $item_discount[$cart_item->id] }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Close</button>
                    <button wire:click="setProductDiscount('{{ $cart_item->rowId }}', {{ $cart_item->id }})"
                        type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
