{{-- Champs d'une proposition : prix total, disponibilite et message.
     Partages par l'envoi et la modification (proposals.box).
     Variable : $proposal (ServiceProposal ou null). --}}
<div class="dt-field">
    <label class="dt-label" for="pp-price"><i class="fa-solid fa-euro-sign"></i> Votre prix total</label>
    <div class="pp-input-group">
        <input type="number" name="price" id="pp-price" class="dt-input" step="0.01" min="1" max="999999"
               value="{{ old('price', $proposal?->price) }}" placeholder="0,00" required>
        <span aria-hidden="true">€</span>
    </div>
</div>

<div class="dt-field">
    <label class="dt-label" for="pp-date">
        <i class="fa-regular fa-calendar"></i> Disponible à partir du <span class="pp-optional">facultatif</span>
    </label>
    <input type="date" name="available_on" id="pp-date" class="dt-input" min="{{ today()->toDateString() }}"
           value="{{ old('available_on', $proposal?->available_on?->toDateString()) }}">
</div>

<div class="dt-field">
    <label class="dt-label" for="pp-message"><i class="fa-regular fa-message"></i> Votre message au client</label>
    <textarea name="message" id="pp-message" class="dt-input pp-textarea" rows="5" minlength="20" maxlength="2000" required
              placeholder="Ce que comprend votre prix, votre expérience, le matériel que vous apportez…">{{ old('message', $proposal?->message) }}</textarea>
</div>
