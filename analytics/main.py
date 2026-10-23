from flask import Flask, jsonify
import mysql.connector

app = Flask(__name__)

# Database configuration
db_config = {
    'host': '127.0.0.1',
    'database': 'ecommerce_db',
    'user': 'root',
    'password': ''
}

@app.route('/analytics/sales-summary', methods=['GET'])
def sales_summary():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        # Total sales & revenue calculation
        cursor.execute("SELECT SUM(total_amount) as total_revenue, COUNT(*) as total_orders FROM orders")
        summary = cursor.fetchone()
        
        cursor.close()
        conn.close()
        
        return jsonify({
            "status": "success",
            "analytics": {
                "total_revenue": summary['total_revenue'] or 0.0,
                "total_orders": summary['total_orders'] or 0
            }
        })
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/recommendations/<int:product_id>', methods=['GET'])
def recommendations(product_id):
    # Simple recommendation logic
    return jsonify({
        "product_id": product_id,
        "recommended_items": [{"id": 2, "name": "Wireless Headphones"}, {"id": 3, "name": "Cotton T-Shirt"}]
    })

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=8001)

