<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFeedback;
use App\Rules\PersonName;
use App\Rules\PlainText;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductFeedbackController extends Controller
{
    public function reviewForm(Request $request, string $type, Product $product): View
    {
        $this->ensureAvailable($type, $product);

        return view('site.products.feedback', [
            'product' => $product,
            'isReview' => true,
            'canReview' => $request->user()->owns($product),
            'feedback' => $product->feedback()->where('review_user_id', $request->user()->id)->first(),
        ]);
    }

    public function submitReview(Request $request, string $type, Product $product): RedirectResponse
    {
        $this->ensureAvailable($type, $product);
        abort_unless($request->user()->owns($product), 403);

        $request->merge([
            'display_name' => PersonName::normalize($request->input('display_name')),
            'content' => trim((string) $request->input('content')),
        ]);
        $data = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:100', new PersonName],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'min:20', 'max:1500', new PlainText],
        ]);

        ProductFeedback::updateOrCreate([
            'product_id' => $product->id,
            'review_user_id' => $request->user()->id,
        ], [
            'user_id' => $request->user()->id,
            'type' => ProductFeedback::TYPE_REVIEW,
            'display_name' => $data['display_name'],
            'rating' => $data['rating'],
            'content' => $data['content'],
            'subject' => null,
            'status' => ProductFeedback::STATUS_PENDING,
            'approved_at' => null,
        ]);

        return redirect(route('account.library.show', ['product' => $product->slug]).'#feedback-heading')
            ->with('feedback_status', 'Obrigado! Sua avaliação foi recebida. Após a aprovação, ela será exibida na página do produto.');
    }

    public function suggestionForm(string $type, Product $product): View
    {
        $this->ensureAvailable($type, $product);

        return view('site.products.feedback', [
            'product' => $product,
            'isReview' => false,
            'canReview' => false,
            'feedback' => null,
        ]);
    }

    public function submitSuggestion(Request $request, string $type, Product $product): RedirectResponse
    {
        $this->ensureAvailable($type, $product);

        $request->merge([
            'display_name' => PersonName::normalize($request->input('display_name')),
            'subject' => trim((string) $request->input('subject')),
            'content' => trim((string) $request->input('content')),
        ]);
        $data = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:100', new PersonName],
            'subject' => ['required', 'string', 'min:5', 'max:120', new PlainText],
            'content' => ['required', 'string', 'min:20', 'max:2000', new PlainText],
        ]);

        $product->feedback()->create([
            'user_id' => $request->user()->id,
            'type' => ProductFeedback::TYPE_SUGGESTION,
            'display_name' => $data['display_name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => ProductFeedback::STATUS_PENDING,
        ]);

        $destination = $request->user()->purchases()->paid()->where('product_id', $product->id)->exists()
            ? route('account.library.show', ['product' => $product->slug]).'#feedback-heading'
            : $product->url.'#avaliacoes';

        return redirect($destination)
            ->with('feedback_status', 'Obrigado! Sua sugestão foi recebida e ficará disponível para análise.');
    }

    private function ensureAvailable(string $type, Product $product): void
    {
        abort_unless($product->isPublished() && $product->type->segment() === $type, 404);
    }
}
