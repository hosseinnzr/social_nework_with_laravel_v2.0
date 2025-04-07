import os
import numpy as np
from PIL import Image
import torch
from torchvision import models, transforms
import faiss  # FAISS library import

# 🔧 مسیر پوشه‌ای که تصاویرت داخلشه (تغییر بده)
IMAGE_FOLDER = "../post-picture"
FEATURE_FOLDER = "features"

# ساخت پوشه ذخیره بردارها اگه وجود نداشته باشه
os.makedirs(FEATURE_FOLDER, exist_ok=True)

# 📦 مدل ResNet50 بدون لایه آخر
model = models.resnet50(pretrained=True)
model = torch.nn.Sequential(*list(model.children())[:-1])  # حذف لایه fully connected
model.eval()

# 🌀 تبدیل تصویر برای ورودی به مدل
transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406],
                         std=[0.229, 0.224, 0.225])
])

# 🎯 تابع استخراج ویژگی
def extract_feature_vector(image_path):
    image = Image.open(image_path).convert('RGB')
    img_tensor = transform(image).unsqueeze(0)
    with torch.no_grad():
        features = model(img_tensor).squeeze().numpy()
    return features / np.linalg.norm(features)  # نرمال‌سازی

# ایجاد ایندکس FAISS (با استفاده از فضای ویژگی‌ها به ابعاد 2048 برای ResNet50)
dim = 2048  # تعداد ویژگی‌ها در مدل ResNet50
index = faiss.IndexFlatL2(dim)  # ایندکس برای جستجوی مشابه‌ترین همسایگان با استفاده از فاصله L2

# پردازش همه تصاویر در پوشه و ذخیره ویژگی‌ها در FAISS index
for filename in os.listdir(IMAGE_FOLDER):
    if filename.lower().endswith(('.jpg', '.jpeg', '.png')):
        image_path = os.path.join(IMAGE_FOLDER, filename)
        feature = extract_feature_vector(image_path)

        # اضافه کردن ویژگی به ایندکس
        feature = np.expand_dims(feature, axis=0).astype(np.float32)  # تبدیل به آرایه 2D
        index.add(feature)  # اضافه کردن ویژگی به ایندکس FAISS

        # ذخیره مسیر تصویر و ویژگی در فایل برای جستجوهای آینده
        feature_path = os.path.join(FEATURE_FOLDER, filename + ".npy")
        np.save(feature_path, feature)
        print(f"[✅] ویژگی ذخیره شد: {feature_path}")

# ذخیره ایندکس FAISS
faiss.write_index(index, "image_index.index")
print("[✅] ایندکس FAISS ذخیره شد: image_index.index")
