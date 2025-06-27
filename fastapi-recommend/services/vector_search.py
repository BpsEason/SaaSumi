from sentence_transformers import SentenceTransformer, util
import numpy as np
import os

# Global variable to hold the loaded model
MODEL_CACHE = {}

def load_model(model_name: str = 'intfloat/multilingual-e5-large'):
    """
    Loads a Sentence Transformer model from the cache or downloads it.
    """
    if 'model' not in MODEL_CACHE:
        print(f"Loading Sentence Transformer model: {model_name}...")
        # Check if model is downloaded locally to avoid redownloading
        model_path = os.path.join(os.path.expanduser('~'), '.cache/torch/sentence_transformers', model_name.replace('/', '_'))
        if os.path.exists(model_path):
            model = SentenceTransformer(model_path)
        else:
            model = SentenceTransformer(model_name)
        MODEL_CACHE['model'] = model
        print("Model loaded successfully.")
    return MODEL_CACHE['model']

def search_similar_rooms(query: str, rooms: list, model, top_k: int = 5):
    """
    Performs a vector similarity search on a list of rooms based on a query.
    """
    # 1. Encode the query
    query_embedding = model.encode(query, convert_to_tensor=True)

    # 2. Encode all room descriptions
    corpus = [room['description'] for room in rooms]
    corpus_embeddings = model.encode(corpus, convert_to_tensor=True)

    # 3. Calculate cosine similarity
    cosine_scores = util.cos_sim(query_embedding, corpus_embeddings)[0]

    # 4. Get the top k results
    top_results_indices = np.argsort(cosine_scores.cpu().numpy())[::-1][:top_k]

    # 5. Format the recommendations, including original room data like image_url
    recommendations = []
    for idx in top_results_indices:
        room_data = rooms[idx]
        recommendations.append({
            'id': room_data['id'],
            'name': room_data['name'],
            'description': room_data['description'],
            'image_url': room_data.get('image_url', ''), # Include image URL
            'score': float(cosine_scores[idx])
        })

    return recommendations

if __name__ == '__main__':
    # Simple test case
    print("Running vector_search.py as a script for testing...")
    
    # Load model
    model = load_model()

    # Sample data (same as in main.py)
    sample_rooms = [
        {"id": 1, "name": "豪華和室套房", "description": "傳統榻榻米房間，配有私人溫泉。享受日式庭園美景。", "image_url": "https://placehold.co/150x100/4A90E2/FFFFFF?text=和室套房"},
        {"id": 2, "name": "現代雙人房", "description": "配備 Netflix 和高速 Wi-Fi 的簡約風格房間，適合商務旅客。", "image_url": "https://placehold.co/150x100/80B3FF/FFFFFF?text=雙人房"},
        {"id": 3, "name": "家庭溫馨四人房", "description": "兩張雙人床，空間寬敞，適合家庭入住。提供兒童遊戲區。", "image_url": "https://placehold.co/150x100/B8D9FF/FFFFFF?text=家庭房"},
        {"id": 4, "name": "海景豪華套房", "description": "面海陽台，擁有絕佳視野。提供客房服務。", "image_url": "https://placehold.co/150x100/C8E3FF/FFFFFF?text=海景套房"},
        {"id": 5, "name": "背包客混合宿舍", "description": "經濟實惠的床位，設有共用廚房和淋浴間。適合年輕旅人。", "image_url": "https://placehold.co/150x100/DAEAFF/FFFFFF?text=混合宿舍"},
        {"id": 6, "name": "日式溫泉小屋", "description": "獨立小木屋，有私人露天風呂，周圍環繞著山林。", "image_url": "https://placehold.co/150x100/EDF6FF/FFFFFF?text=溫泉小屋"},
    ]
    
    query_keywords = "找一個有溫泉的日式房間"
    print(f"\nSearching for: '{query_keywords}'")
    
    recommendations = search_similar_rooms(query_keywords, sample_rooms, model, top_k=3)
    
    print("\nTop 3 recommendations:")
    for rec in recommendations:
        print(f"  - ID: {rec['id']}, Name: {rec['name']}, Score: {rec['score']:.4f}, Image: {rec['image_url']}")
        print(f"    Description: {rec['description']}")
