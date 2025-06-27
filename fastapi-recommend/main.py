from fastapi import FastAPI, Request, HTTPException
from fastapi.responses import HTMLResponse
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel
from typing import List, Dict
import os

from services.vector_search import load_model, search_similar_rooms

# Load the Sentence Transformer model once when the app starts
model = load_model()

# Initialize FastAPI app
app = FastAPI(
    title="AI Recommendation Engine",
    description="Content-based recommendation API for hotel rooms.",
    version="1.0.0",
)

# Template for simple HTML response
templates = Jinja2Templates(directory=".")

class RecommendationRequest(BaseModel):
    keywords: str
    limit: int = 5

class RecommendationResponse(BaseModel):
    recommendations: List[Dict]

@app.get("/", response_class=HTMLResponse)
async def home(request: Request):
    """
    A simple home page for the API.
    """
    return templates.TemplateResponse("index.html", {"request": request})

@app.post("/api/recommend", response_model=RecommendationResponse)
async def get_recommendations(request: RecommendationRequest):
    """
    Endpoint to get room recommendations based on Japanese keywords.
    """
    if not request.keywords:
        raise HTTPException(status_code=400, detail="Keywords cannot be empty.")

    # In a real-world scenario, this data would come from a database (e.g., MySQL via Laravel Backend)
    # For this example, we use a static list of rooms.
    # Added image_url to room data for frontend display
    sample_rooms = [
        {"id": 1, "name": "豪華和室套房", "description": "傳統榻榻米房間，配有私人溫泉。享受日式庭園美景。", "image_url": "https://placehold.co/150x100/4A90E2/FFFFFF?text=和室套房"},
        {"id": 2, "name": "現代雙人房", "description": "配備 Netflix 和高速 Wi-Fi 的簡約風格房間，適合商務旅客。", "image_url": "https://placehold.co/150x100/80B3FF/FFFFFF?text=雙人房"},
        {"id": 3, "name": "家庭溫馨四人房", "description": "兩張雙人床，空間寬敞，適合家庭入住。提供兒童遊戲區。", "image_url": "https://placehold.co/150x100/B8D9FF/FFFFFF?text=家庭房"},
        {"id": 4, "name": "海景豪華套房", "description": "面海陽台，擁有絕佳視野。提供客房服務。", "image_url": "https://placehold.co/150x100/C8E3FF/FFFFFF?text=海景套房"},
        {"id": 5, "name": "背包客混合宿舍", "description": "經濟實惠的床位，設有共用廚房和淋浴間。適合年輕旅人。", "image_url": "https://placehold.co/150x100/DAEAFF/FFFFFF?text=混合宿舍"},
        {"id": 6, "name": "日式溫泉小屋", "description": "獨立小木屋，有私人露天風呂，周圍環繞著山林。", "image_url": "https://placehold.co/150x100/EDF6FF/FFFFFF?text=溫泉小屋"},
    ]

    try:
        recommendations = search_similar_rooms(
            query=request.keywords,
            rooms=sample_rooms,
            model=model,
            top_k=request.limit
        )
        # Add a placeholder for AI explanation if needed in the future
        for rec in recommendations:
            rec['explanation'] = "此推薦基於您的關鍵詞與房間描述的語義相似度。" # Simplified explanation

        return {"recommendations": recommendations}
    except Exception as e:
        # Log the error and return a 500 response
        print(f"Error during recommendation: {e}")
        raise HTTPException(status_code=500, detail="An error occurred while processing the request.")
