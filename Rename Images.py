import os

# Supported image extensions
image_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.tiff']

# Get all image files in current directory
images = [f for f in os.listdir() if os.path.splitext(f)[1].lower() in image_extensions]

# Sort to keep consistent order
images.sort()

# Rename images
for index, filename in enumerate(images, start=1):
    ext = os.path.splitext(filename)[1].lower()
    new_name = f"{index}{ext}"
    os.rename(filename, new_name)
    print(f"Renamed '{filename}' to '{new_name}'")

print("Renaming complete.")
