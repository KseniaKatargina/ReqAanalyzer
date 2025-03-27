from quart import Quart
from quart_cors import cors
from app.celery_app import celery
from app.routes import text_analysis_bp

def create_app():
    app = Quart(__name__)
    cors(app, allow_origin=["http://localhost:8080", "http://127.0.0.1:8080"])

    app.config.update(
        CELERY_BROKER_URL="pyamqp://guest@localhost//",
        CELERY_RESULT_BACKEND="rpc://",
    )
    celery.conf.update(app.config)
    app.register_blueprint(text_analysis_bp)

    return app











