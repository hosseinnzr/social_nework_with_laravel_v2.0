import tensorflow as tf
from tensorflow.keras.applications import ResNet50
from tensorflow.keras.applications.resnet50 import preprocess_input
from tensorflow.keras.preprocessing import image
import numpy as np
import os
import pickle
import csv
from sklearn.metrics.pairwise import cosine_similarity

# مسیر پوشه تصاویر
image_path = r"C:\Users\nazar\Documents\GitHub\social_nework_with_laravel_v2.0\public\post-picture"
features_file = r"C:\Users\nazar\Documents\GitHub\social_nework_with_laravel_v2.0\public\explore_algorithm\files\features.pkl"
csv_file = r"C:\Users\nazar\Documents\GitHub\social_nework_with_laravel_v2.0\public\explore_algorithm\files\ordered_images.csv"


if not os.path.exists(image_path):
    raise FileNotFoundError(f"Error: The directory '{image_path}' does not exist. Please check the path.")

# لود مدل بدون لایه‌های بالایی
model = ResNet50(weights='imagenet', include_top=False, pooling='avg')

# خواندن نام فایل‌های تصویری
image_files = [f for f in os.listdir(image_path) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp'))]
print(f"Found {len(image_files)} images.")

# تابع خواندن و پیش‌پردازش تصویر
def preprocess_image(img_path):
    img = image.load_img(img_path, target_size=(224, 224))
    img_array = image.img_to_array(img)
    img_array = preprocess_input(img_array)  # اعمال پیش‌پردازش ResNet50
    return img_array  # برگرداندن داده‌ها در شکل (224, 224, 3)

# بارگذاری ویژگی‌ها از فایل features.pkl
if os.path.exists(features_file):
    with open(features_file, 'rb') as f:
        features_dict = pickle.load(f)
else:
    features_dict = {}

# ایجاد لیست ویژگی‌ها
features_list = []

# استخراج ویژگی‌های تصاویر جدید و اضافه کردن به فایل
batch_size = 16  # تنظیم اندازه‌ی مناسب برای پردازش سریع‌تر
num_batches = (len(image_files) + batch_size - 1) // batch_size  # تعداد کل batchها

for i in range(num_batches):
    batch_files = image_files[i * batch_size:(i + 1) * batch_size]
    batch_paths = [os.path.join(image_path, img) for img in batch_files]

    # پردازش تمام تصاویر در این batch
    batch_images = np.array([preprocess_image(img) for img in batch_paths])  # شکل (batch_size, 224, 224, 3)

    # استخراج ویژگی‌ها
    batch_features = model.predict(batch_images)  # خروجی (batch_size, 2048)
    
    # ذخیره ویژگی‌ها به همراه نام تصویر
    for j, img in enumerate(batch_files):
        if img not in features_dict:  # بررسی برای جلوگیری از پردازش دوباره تصاویر
            features_dict[img] = batch_features[j]
            features_list.append((img, batch_features[j]))

# ذخیره ویژگی‌ها در فایل features.pkl
with open(features_file, 'wb') as f:
    pickle.dump(features_dict, f)

# مرتب‌سازی تصاویر بر اساس بیشترین شباهت کسینوسی
ordered_images = []
if os.path.exists(csv_file):
    with open(csv_file, mode='r') as file:
        reader = csv.reader(file)
        ordered_images = [row[0] for row in reader]

remaining_images = set(image_files) - set(ordered_images)

while remaining_images:
    max_similarity = -1
    next_img = None

    # پیدا کردن تصویر بعدی با بیشترین شباهت
    current_img = ordered_images[-1] if ordered_images else image_files[0]
    current_features = features_dict[current_img]
    for img in remaining_images:
        similarity = cosine_similarity([current_features], [features_dict[img]])[0][0]
        if similarity > max_similarity:
            max_similarity = similarity
            next_img = img

    # اضافه کردن تصویر انتخاب شده
    ordered_images.append(next_img)
    remaining_images.remove(next_img)

# ذخیره نتایج در فایل CSV با انکودینگ UTF-8
with open(csv_file, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    for img in ordered_images:
        writer.writerow([img])


# نمایش نتایج
print("\nOrdered images based on similarity:")
print(ordered_images)


# add date to milvus DataBase
from pymilvus import Collection, CollectionSchema, FieldSchema, DataType, connections

# اتصال به Milvus
connections.connect("default", host="localhost", port="19530")

# تعریف فیلدهای مجموعه
fields = [
    FieldSchema(name="image_name", dtype=DataType.VARCHAR, max_length=255, is_primary=True),  # فیلد نام تصویر
    FieldSchema(name="image_vector", dtype=DataType.FLOAT_VECTOR, dim=128)  # فیلد برداری (با ابعاد 128)
]

# ایجاد اسکیمای مجموعه
schema = CollectionSchema(fields, "Image data collection")

# ایجاد مجموعه
collection = Collection("image_data", schema)


from pymilvus import utility

# بررسی وجود ایندکس
if not collection.has_index():
    print("Creating index...")
    index_params = {
        "index_type": "IVF_FLAT",  # روش ایندکس (می‌توان IVF_PQ یا HNSW را هم تست کرد)
        "metric_type": "COSINE",   # متریک شباهت (L2 هم قابل استفاده است)
        "params": {"nlist": 100}   # تعداد لیست‌های تقسیم‌بندی برای جستجو
    }
    collection.create_index("image_vector", index_params)
    print("Index created successfully.")

# بارگذاری مجموعه در حافظه
collection.load()
print("Collection loaded successfully.")

import numpy as np
image_vectors = [np.random.random(128).tolist() for _ in ordered_images]

# ذخیره داده‌ها در Milvus
entities = [
    ordered_images,   # لیست نام تصاویر
    image_vectors     # لیست بردارهای ویژگی تصاویر
]
collection.insert(entities)
print("Data inserted into Milvus.")
